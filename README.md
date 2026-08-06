# faustop-healthcheck

> ô loco meu
> tá pegando fogo bicho

![tá pegando fogo bicho](docs/faustao.gif)

Healthcheck de `liveness` e `readiness` para Laravel. São duas perguntas
diferentes, e usar a resposta errada mata container saudável.

## Rotas

| rota | nome | verifica | usar para |
| --- | --- | --- | --- |
| `/health-check/liveness` | `web.health-check.liveness` | só que o processo PHP responde | **healthcheck do container** |
| `/api/health-check/liveness` | `api.health-check.liveness` | idem | idem |
| `/health-check/readiness` | `web.health-check.readiness` | Redis, banco e storage | balanceador / borda / monitoração |
| `/api/health-check/readiness` | `api.health-check.readiness` | idem | idem |

### `liveness`

Responde `200` com `{"status":"alive","timestamp":"..."}`. Não toca em Redis,
banco, storage nem em nenhum serviço externo — só prova que o PHP está de pé.

**É esta a rota para o `HEALTHCHECK` do container.** O healthcheck do container
decide se o processo morre e é reagendado; se ele consultar uma dependência
externa, uma oscilação do Redis ou do banco derruba container saudável, e o
reschedule em massa que vem depois é o que estoura a rede overlay.

Ao mexer neste pacote: **nada de dependência externa dentro do `liveness()`**.

### `readiness`

Verifica Redis, banco e storage. Com todas as dependências habilitadas de pé,
responde `200`:

```json
{"redis":"Running at ...","DB":"Running at ...","storage":"Running at ..."}
```

Dependência desabilitada vem com string vazia. Dependência fora vem como
`"down"`, e aí a resposta é **`503`**:

```json
{"redis":"down","DB":"Running at ...","storage":""}
```

Se a verificação lançar exceção, a resposta é `503` com a dependência que
quebrou marcada como `"down"`, mais o host e a classe da exceção:

```json
{"redis":"down","DB":"","storage":"","host":"api.1.k3f9","error":"ConnectionException"}
```

A mensagem da exceção vai só para o log, nunca para o corpo — esta rota é
alcançável da borda em alguns apps, e mensagem de erro de PDO pode carregar
host e usuário do banco.

`503` e não `500` porque o PHP respondeu; quem falhou foi a dependência. Isso
separa, na agregação por status da monitoração, dependência oscilando de bug de
aplicação.

> **Mudou em 0.10.0:** antes, dependência que respondia falsy **sem lançar
> exceção** caía no retorno normal — corpo dizendo `"down"` com status `200`.
> Um balanceador que olha só o status mandava tráfego para instância com o
> Redis fora. Agora qualquer `"down"` responde `503`. O corpo não mudou: mesmas
> chaves, mesmos valores textuais.

Serve para decidir se a instância deve **receber tráfego** — balanceador, borda
do Traefik, monitoração. Não serve para decidir se o container morre.

## Variáveis

#### obrigatórias

- `REDIS_HEALTH=true`
- `DB_HEALTH=true`

#### opcionais

- `STORAGE_HEALTH=true` - Habilita verificação de montagem do volume storage (existência do diretório). Desativado por padrão.
- `STORAGE_HEALTH_PATH=framework/cache` - Subpath relativo a `storage/` para verificar (padrão: `framework/cache`)

Todas valem só para o `readiness`. O `liveness` ignora todas.
