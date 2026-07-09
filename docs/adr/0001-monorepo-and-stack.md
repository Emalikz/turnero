# ADR 0001: Monorepo y stack principal

## Estado

Aprobado

## Contexto

El producto necesita evolucionar como SaaS con backend, frontend y documentacion versionados juntos para acelerar el arranque y mantener cambios coordinados.

## Decision

Se usara un monorepo con dos aplicaciones principales:

- `backend`: Laravel 12 API-first.
- `frontend`: Vue 3 + Vite + PrimeVue v4.

La infraestructura local se versiona en el mismo repositorio con Docker Compose.

## Consecuencias

- Mejor trazabilidad entre cambios de API y UI.
- Onboarding mas simple.
- Pipelines deben contemplar builds separados para backend y frontend.
