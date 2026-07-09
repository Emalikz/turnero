# ADR 0003: Tiempo real y procesamiento asincrono

## Estado

Aprobado

## Contexto

Las pantallas publicas y la operacion de turnos necesitan actualizacion en tiempo real, y varias tareas deben ejecutarse fuera del request principal.

## Decision

Se usara Redis como backend de colas y broadcasting. Laravel Horizon gestionara workers y monitoreo. Laravel Reverb se usara para WebSockets autoalojados.

Adicionalmente, la cache se usara siempre que sea viable para reducir latencia y carga de consultas repetitivas. Esto aplica especialmente a configuraciones globales, configuraciones de tenant, catalogos de apoyo, dashboards y lecturas de alto volumen que no requieran consistencia inmediata en cada request.

## Consecuencias

- Menor dependencia de proveedores externos.
- Se debe cuidar observabilidad y capacidad de conexiones concurrentes.
- La experiencia local necesita Docker o servicios instalados para Redis y Reverb.
- La aplicacion debe definir invalidacion de cache por dominio funcional para evitar datos obsoletos.
