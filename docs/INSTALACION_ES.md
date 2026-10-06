# Instalación y uso — CoursePulse

## Instalar

1. En una copia de pruebas de Moodle 5, entra como administrador.
2. Administración del sitio → Plugins → Instalar plugins → sube `report_coursepulse-0.3.0-beta.zip`.
3. Comprueba el componente `report_coursepulse` y completa la actualización.
4. Si instalas manualmente: Moodle 5.0 `report/coursepulse`; Moodle 5.1 `public/report/coursepulse`. Copia ahí el contenido del directorio `coursepulse`, sin anidar otro directorio.
5. Abre un curso → Informes → CoursePulse — Dashboard del curso.

## Preparar los datos

Activa el seguimiento de finalización en sitio y curso. Configura la finalización de cuestionarios, tareas y demás actividades que quieras medir. Las actividades sin seguimiento no entran en el porcentaje. Los registros históricos solo aportan eventos que Moodle haya conservado.

Comprueba Administración del sitio → Plugins → Registro → Gestionar almacenes: registro estándar habilitado. No necesitas Composer, claves API ni modificar MySQL. El plugin no instala tablas y no incorpora tareas cron.

## Ajustes

Administración del sitio → Plugins → Informes → CoursePulse.

- Umbral entre eventos: 15 minutos por defecto.
- Seguimiento por inactividad: 7 días.
- Riesgo alto por inactividad: 14 días. Se aplica el mayor entre seguimiento y riesgo alto si se configuran al revés.
- Período de gracia: 7 días desde matrícula activa o inicio del curso, lo que ocurra después.

Los cursos que aún no empiezan o ya terminaron se etiquetan fuera de fechas; no se clasifican por inactividad. Las actividades completadas se reconocen incluso fuera de esas fechas.

## Permisos

Para el panel externo y roles de seguimiento por categoría, consulta [ANALISTA_ES.md](ANALISTA_ES.md).

`report/coursepulse:view`: docentes, docentes editores y gestores.
`report/coursepulse:export`: docentes editores y gestores.
`report/coursepulse:viewips`: gestores por defecto. Puedes asignarlo a otro rol según tu organización.

En grupos separados, un docente sin acceso a todos los grupos solo puede revisar sus grupos. El mismo alcance se aplica al detalle y a la exportación.

## Lectura del dashboard

Las tarjetas cubren el curso/grupo completo, aun cuando filtras un nombre o estado. La tabla refleja el filtro y la página. La duración y el promedio se calculan solo para los estudiantes de esa página. El CSV exporta esa página. El botón Descargar Excel exporta todos los estudiantes que coinciden con los filtros, hasta 5.000, en dos hojas: Resumen y Estudiantes. Para selecciones mayores, filtra por grupo. Los porcentajes y duraciones son valores numéricos; puedes calcular promedios en Excel. Si se supera el límite de 100.000 eventos, los tiempos se dejan vacíos y se indica el motivo en Resumen.

Las fechas filtran eventos y duración, no el avance histórico. El avance mostrado es actual. Entra al nombre de un estudiante para ver sus actividades por sección y sus últimas IP, si tu rol tiene permiso.

Completar una actividad no implica aprobarla. Un tiempo de cero puede corresponder a un solo evento o a registros eliminados. El riesgo de abandono necesita revisión humana.

## Actualizar desde versiones anteriores

Sube el nuevo ZIP como actualización del plugin existente y completa Administración del sitio → Notificaciones. Después purga las cachés en Administración del sitio → Desarrollo → Purgar cachés para recargar estilos y traducciones. No desinstales el plugin para actualizar.

## Desinstalar

Administración del sitio → Plugins → Vista general de plugins → CoursePulse → Desinstalar. El plugin no borra matrículas, calificaciones, finalización ni registros originales.
