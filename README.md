# faustop-healthcheck

> ô loco meu
> tá pegando fogo bicho


#### mandatory vars

- `REDIS_HEALTH=true`
- `DB_HEALTH=true`

#### optional vars

- `STORAGE_HEALTH=true` - Habilita verificação de montagem do volume storage (existência do diretório). Desativado por padrão.
- `STORAGE_HEALTH_PATH=framework/cache` - Subpath relativo a `storage/` para verificar (padrão: `framework/cache`)
