# Turnero

Aplicacion SaaS multitenant para gestion de turnos, citas y pantallas publicas.

## Arquitectura

- Multi-tenant por schema en PostgreSQL.
- API REST en Laravel 12.
- Frontend SPA en Vue 3 + PrimeVue v4.
- Redis para cache, colas y broadcasting.
- Laravel Reverb para tiempo real.

## Estructura

- `backend/`
- `frontend/`
- `docs/adr/`
- `docker/`
- `openspec/`

## Inicio rapido

1. Copiar variables desde `backend/.env.example`.
2. Levantar servicios con `docker compose up --build`.
3. Instalar dependencias del frontend si trabajas fuera de Docker con `npm install` en `frontend`.

## Estado actual

Este repositorio contiene el foundation inicial del producto: monorepo, base multi-tenant, healthcheck de API, shell del frontend y documentacion de arquitectura.
