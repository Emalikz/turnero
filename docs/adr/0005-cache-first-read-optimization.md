# ADR 0005: Cache first para lecturas viables

## Estado

Aprobado

## Contexto

El producto operara como SaaS multitenant con consultas repetitivas sobre configuraciones, tableros y datos de soporte. Sin una estrategia explicita de cache, el crecimiento de tenants y usuarios aumentara la carga sobre PostgreSQL y degradara tiempos de respuesta.

## Decision

Se adopta una politica de uso preferente de cache para lecturas siempre que sea viable hacerlo sin comprometer la consistencia funcional.

Redis sera la capa principal de cache y se usara en:

- configuraciones globales del SaaS
- configuraciones visuales y operativas por tenant
- catalogos y metadatos de lectura frecuente
- agregados y resumenes de dashboard
- respuestas derivadas costosas o repetitivas

No se aplicara cache en operaciones donde el dato deba reflejar cambios criticos de manera inmediata sin una estrategia clara de invalidacion.

## Consecuencias

- Menor carga sobre base de datos en rutas de lectura frecuentes.
- Mejor latencia para dashboards, modulos administrativos y configuraciones.
- Cada modulo nuevo debe evaluar si su lectura principal debe salir desde cache.
- La implementacion debe incluir claves por tenant cuando corresponda e invalidacion explicita ante cambios.
