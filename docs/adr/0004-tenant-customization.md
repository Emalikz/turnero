# ADR 0004: Personalizacion visual por tenant

## Estado

Aprobado

## Contexto

El producto debe diferenciarse por alto nivel de personalizacion visual sin duplicar codigo por cliente.

## Decision

La configuracion visual de cada tenant se almacenara como datos estructurados y no como codigo. El frontend consumira tokens de marca, configuracion de layout y layouts publicos serializados.

## Consecuencias

- Es posible construir un page builder incremental.
- El frontend debe soportar tokens dinamicos y layouts configurables.
- Conviene versionar el formato de configuracion visual.
