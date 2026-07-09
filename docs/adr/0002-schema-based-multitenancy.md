# ADR 0002: Multitenancy por schema

## Estado

Aprobado

## Contexto

Se requiere aislamiento fuerte entre tenants sin asumir el costo operativo de una base de datos por cliente.

## Decision

Cada tenant tendra un schema propio en PostgreSQL. El schema `public` almacenara metadatos globales, catalogo de tenants y configuracion compartida.

La API resolvera el tenant por dominio o encabezado y ajustara el `search_path` de la conexion activa.

## Consecuencias

- Aislamiento fuerte con costos razonables.
- Las migraciones deben dividirse en globales y por tenant.
- Los tests deben cubrir cambio de contexto de schema.
