# Turnero SaaS

## Stack

- Backend: Laravel 12, PHP 8.4, PostgreSQL, Redis, Horizon, Reverb
- Frontend: Vue 3, TypeScript, Vite, PrimeVue v4, Pinia, Vue Router
- Infraestructura: Docker Compose para desarrollo, DigitalOcean para despliegue

## Reglas de implementacion

- Mantener una arquitectura API-first entre `backend` y `frontend`.
- Todo dato de tenant vive en schemas dedicados de PostgreSQL.
- El schema `public` se usa solo para metadatos globales y catalogo de tenants.
- Los endpoints deben responder JSON consistente con `data`, `meta` y `error` cuando aplique.
- La logica de negocio compleja no va en controladores.

## Estructura esperada

- `backend/`: API Laravel y procesos de colas / broadcasting.
- `frontend/`: SPA Vue para administracion y operacion.
- `docs/adr/`: decisiones de arquitectura.
- `docker/`: imagenes y configuracion local.

## Verificacion minima

- Backend: `php artisan test`
- Frontend: `npm run build`
- Integracion local: `docker compose up --build`
