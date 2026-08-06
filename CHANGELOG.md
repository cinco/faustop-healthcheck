# Changelog

## [0.10.0] — 2026-08-06

### Added

- Rota `liveness` de verdade: `liveness()` prova apenas que o processo PHP responde, sem nenhuma dependência externa — sem Redis, sem banco, sem storage. É a rota para a qual o `HEALTHCHECK` do container deve apontar.
- `.gitattributes` com `export-ignore` para manter `/docs`, `/.gitattributes` e `/.gitignore` fora do tarball de dist que o Composer baixa.

### Changed

- `readiness` em falha responde `503` em vez de `500`. O PHP respondeu; quem falhou foi a dependência, e separar os dois torna oscilação de dependência distinguível de bug de aplicação na agregação por status.
- Corpo de erro do `readiness` passa a ser JSON, sem HTML e sem requisição a recurso externo. A mensagem da exceção fica só no log — a rota é alcançável da borda em alguns apps, e erro de PDO pode carregar host e usuário do banco.
- README documenta a diferença entre as duas rotas, para qual delas o healthcheck do container deve apontar, e os três corpos possíveis do `readiness`.
- O GIF do README passou a ser servido do próprio repositório (`docs/faustao.gif`, otimizado de 3,3 MB para 1,1 MB) em vez de hotlink.

### Fixed

- `liveness` e `readiness` apontavam para o mesmo método, que consulta Redis, banco e storage. Na prática `liveness` não existia, e uma oscilação de dependência derrubava container saudável.
- `readiness` respondia `200` quando uma dependência habilitada retornava falsy sem lançar exceção — o corpo dizia `"down"` e o status dizia pronto, então um balanceador seguia mandando tráfego para a instância. Qualquer `"down"` agora responde `503`.
- O `catch` usava `$_SERVER['HOSTNAME']` e `$_SERVER['APP_NAME']` sem checar existência, então o próprio handler de erro quebrava quando faltavam.
- O GIF do README apontava para `j.gifs.com`, que hoje responde `400`.

### Compatibilidade

- URIs e os quatro nomes de rota preservados (`api.health-check.liveness`, `web.health-check.readiness`, etc.).
- Corpo de sucesso do `readiness` inalterado: mesmas chaves (`redis`, `DB`, `storage`) e mesmos valores textuais.
- `healthCheck()` mantido como alias `@deprecated` de `readiness()`, para apps que apontem rota própria para o método.
