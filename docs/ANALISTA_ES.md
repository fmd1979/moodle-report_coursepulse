# Acceso externo para analistas de seguimiento

Desde la versión 0.3.0-beta, `/report/coursepulse/overview.php` abre el panel fuera de los cursos. También se puede abrir `/report/coursepulse/index.php` sin `id`. No requiere matrícula, rol docente ni administración: requiere los permisos explícitos de CoursePulse.

## Configurar el rol

1. Administración del sitio → Usuarios → Permisos → Definir roles → Añadir un nuevo rol. Comenzar sin arquetipo; nombre «Analista de seguimiento». Habilitar asignaciones en contexto de categoría (y curso si se necesita asignación puntual).
2. Permitir `report/coursepulse:viewoverview` para abrir el panel de categorías y `report/coursepulse:view` para consultar los resúmenes de cursos.
3. Para permitir nombres, métricas individuales y detalle de actividades, permitir `report/coursepulse:viewstudents`. Sin este permiso, el reporte de curso muestra solo resúmenes y gráficas de estado/avance; no permite búsquedas por nombre, datos individuales ni exportación de estudiantes.
4. Opcional: permitir `report/coursepulse:export` para Excel/CSV, junto con `viewstudents`. Permitir `report/coursepulse:viewips` únicamente si debe consultar direcciones IP (requiere acceso individual). No se incluyen IP en Excel.
5. Si debe analizar todos los grupos de los cursos de esa categoría, permitir `moodle/site:accessallgroups` en ese ámbito. Sin él, los cursos con grupos separados requieren pertenencia al grupo y selección dentro del reporte; sus datos no se suman en el panel de categorías.
6. Asignar el rol al usuario desde la categoría correspondiente → Asignar roles. La asignación se hereda en sus subcategorías y cursos; no abre categorías hermanas. Una prohibición en un curso sigue vigente. Los cursos ocultos requieren adicionalmente `moodle/course:viewhiddencourses`.
7. Después de actualizar el plugin, entrar a Notificaciones y purgar cachés para registrar los nuevos permisos. Los roles existentes reciben `viewstudents` a partir de sus permisos previos de `view`; revisar los roles personalizados para elegir su nivel de acceso.

No otorgar permisos de edición, administración ni `moodle/course:view` para usar estos reportes.

## Entrada visible fuera del curso

El plugin añade un enlace en los ajustes de categoría y en la navegación de portada cuando el tema la muestra. Para una entrada visible en Boost u otros temas, el administrador puede añadir al menú personalizado:

```
Seguimiento académico|/report/coursepulse/overview.php
```

La visibilidad del enlace no concede acceso. Cada petición comprueba permisos. El usuario puede guardar también la URL como favorito.

## Qué muestra

- Selector de categorías autorizadas.
- Comparación por curso: matrículas activas, avance medio cuando hay actividades con finalización, alertas, alta inactividad, nunca ingresó y actividades completadas.
- Tarjetas y gráfica de seguimiento de los cursos de la página actual, hasta 25 por página. Son matrículas por curso, no estudiantes únicos; los totales no representan toda la categoría si hay varias páginas.
- Entrada a cada curso y, con permiso individual, a cada estudiante; filtros de fechas, actividades, estimación de tiempo, sesiones e IP según permisos. Excel disponible por curso, no como consolidado de categoría en esta versión.

Los indicadores son señales para seguimiento, no una declaración de deserción. El panel de categoría evita recorrer registros de conexión de todos los cursos en una sola petición.
