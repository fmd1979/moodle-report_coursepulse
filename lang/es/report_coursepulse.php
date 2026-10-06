<?php
// This file is part of Moodle - https://moodle.org/
// Moodle is free software: you can redistribute it and/or modify it under the
// terms of the GNU General Public License as published by the Free Software
// Foundation, either version 3 of the License, or (at your option) any later version.
// Moodle is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
// without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
/**
 * CoursePulse course engagement dashboard.
 *
 * @package report_coursepulse
 * @copyright 2026 Franklin David Moya Davila / SiteEcuador
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
$string['pluginname'] = 'CoursePulse — Dashboard del curso';
$string['coursepulse:view'] = 'Ver el dashboard de participación del curso';
$string['coursepulse:viewips'] = 'Ver las IP de conexión de estudiantes';
$string['coursepulse:export'] = 'Exportar datos de participación';
$string['privacy:metadata'] = 'CoursePulse consulta matrícula, finalización, último acceso y registros estándar de Moodle. No almacena datos personales adicionales ni envía datos a servicios externos.';
$string['sessiongap'] = 'Umbral de inactividad (minutos)';
$string['sessiongap_desc'] = 'Un intervalo mayor inicia otra sesión estimada. Una sesión con un único evento tiene duración medible de cero.';
$string['warningdays'] = 'Días sin acceso al curso: seguimiento';
$string['criticaldays'] = 'Días sin acceso al curso: riesgo alto';
$string['gracedays'] = 'Período de gracia desde matrícula o inicio del curso (días)';
$string['from'] = 'Eventos desde';
$string['to'] = 'Eventos hasta';
$string['risk'] = 'Estado de seguimiento';
$string['search'] = 'Buscar por nombre del estudiante';
$string['invaliddates'] = 'Selecciona un período pasado o actual de hasta 90 días, con fin igual o posterior al inicio.';
$string['notstudent'] = 'El estudiante no pertenece a las matrículas activas del curso y grupo autorizados.';
$string['risk_active'] = 'Acceso reciente al curso';
$string['risk_warning'] = 'Requiere seguimiento';
$string['risk_critical'] = 'Riesgo alto por inactividad';
$string['risk_never'] = 'Sin acceso registrado al curso';
$string['risk_grace'] = 'En período de gracia / curso fuera de fechas';
$string['risk_completed'] = 'Todas las actividades con seguimiento completadas';
$string['methodology'] = 'El tiempo es una estimación entre eventos consecutivos del curso; no demuestra tiempo efectivo de estudio. La inactividad señala necesidad de seguimiento, no deserción confirmada. El avance considera actividades visibles con finalización; completar con reprobación cuenta como completado, no como aprobado. El avance y la inactividad corresponden al estado actual; las fechas filtran únicamente eventos, tiempo e IP.';
$string['nocompletion'] = 'No existen actividades visibles con seguimiento de finalización. El avance no está disponible; activa la finalización del curso y las actividades.';
$string['nologs'] = 'El lector de registros estándar no está habilitado. No están disponibles el tiempo ni el historial de conexiones.';
$string['truncated'] = 'Se alcanzó el límite de 100.000 eventos. Las estimaciones de tiempo se ocultan para evitar resultados incompletos. Reduce el período o abre un estudiante.';
$string['summary_total'] = 'Matrículas activas';
$string['summary_active'] = 'Acceso reciente';
$string['summary_warning'] = 'Requieren seguimiento';
$string['summary_critical'] = 'Riesgo alto por inactividad';
$string['summary_never'] = 'Sin acceso registrado';
$string['summary_completed'] = 'Actividades con seguimiento completadas';
$string['meanprogress'] = 'Avance promedio de finalización (todo el grupo autorizado): {$a}%';
$string['students'] = 'Estudiantes';
$string['student'] = 'Estudiante';
$string['progress'] = 'Avance de finalización';
$string['lastaccess'] = 'Último acceso al curso';
$string['estimatedtime'] = 'Tiempo estimado (hh:mm:ss)';
$string['sessions'] = 'Sesiones estimadas';
$string['averagesession'] = 'Sesión promedio (hh:mm:ss)';
$string['events'] = 'Eventos del curso';
$string['section'] = 'Sección';
$string['activity'] = 'Actividad';
$string['completion'] = 'Estado de finalización';
$string['complete'] = 'Completada';
$string['passed'] = 'Completada, aprobada';
$string['failed'] = 'Completada, reprobada';
$string['pending'] = 'Pendiente';
$string['connections'] = 'Últimos 100 eventos del curso con IP de conexión';
$string['ip'] = 'Dirección IP';
$string['origin'] = 'Origen';
$string['back'] = 'Volver al dashboard';
$string['ipnotice'] = 'Son IP de eventos, que no necesariamente corresponden a inicios de sesión. Redes compartidas, proxies y VPN pueden compartir o cambiar una IP. La IP no identifica por sí sola a una persona.';
$string['notavailable'] = 'No disponible';
$string['pagemean'] = 'Tiempo estimado promedio de los estudiantes de esta página (incluye quienes no tienen eventos): {$a}';
$string['pagescope'] = 'Las tarjetas y el gráfico de avance abarcan todo el curso/grupo autorizado. Las estadísticas en pantalla y el CSV abarcan la página mostrada. Excel exporta todos los estudiantes filtrados (máximo 5.000). La ausencia de eventos también puede deberse a registros eliminados; duración cero no demuestra ausencia de estudio.';
$string['exportpage'] = 'Exportar esta página a CSV';
$string['eventtrend'] = 'Evolución diaria de eventos (estudiantes de esta página)';
$string['exportexcel'] = 'Descargar Excel (.xlsx) — todos los filtrados';
$string['excelsummary'] = 'Resumen';
$string['excelscope'] = 'Alcance del resumen';
$string['excelscope_desc'] = 'Las cifras de resumen corresponden a todo el curso/grupo autorizado. La hoja de estudiantes incluye todos los que coinciden con los filtros, hasta 5.000. Las fechas filtran eventos y tiempo; el avance es actual.';
$string['exportedstudents'] = 'Estudiantes exportados';
$string['measurement'] = 'Medición de tiempo';
$string['excellimit'] = 'La exportación Excel admite hasta 5.000 estudiantes. Selecciona un grupo o aplica filtros para reducir la selección.';
$string['statuschart'] = 'Estado de seguimiento del curso/grupo';
$string['progresschart'] = 'Distribución del avance del curso/grupo';
$string['short_active'] = 'Acceso reciente';
$string['short_warning'] = 'Seguimiento';
$string['short_critical'] = 'Riesgo alto';
$string['short_never'] = 'Sin acceso';
$string['short_grace'] = 'En gracia / fuera de fechas';
$string['short_completed'] = 'Completados';
