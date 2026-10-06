# Repositorio Git

Nombre recomendado: `moodle-report_coursepulse` en la cuenta `fmd1979`.

La entrega incluye un archivo `.bundle` con el historial Git y etiqueta `v0.1.0-beta`. Ese archivo permite recuperar un repositorio completo sin perder el commit inicial.

## Recuperar y subir

El repositorio público ya está creado en https://github.com/fmd1979/moodle-report_coursepulse. Para una copia de trabajo normal:\n\n```bash\ngit clone https://github.com/fmd1979/moodle-report_coursepulse.git\n```\n\nPara recuperar el historial local original desde el bundle:

```bash
git clone coursepulse-0.1.0-beta.bundle moodle-report_coursepulse
cd moodle-report_coursepulse
git remote remove origin
git remote add origin https://github.com/fmd1979/moodle-report_coursepulse.git
git fetch origin\n# El bundle conserva el historial local original; el repositorio remoto\n# tiene su propia inicialización mediante la API de GitHub.\n# Trabaja desde un clon del remoto para evitar historias divergentes.
```

La URL corresponde al repositorio público del proyecto. Usa tu autenticación GitHub habitual. No compartas tokens por el chat.

Habilita Issues y revisa Actions. La configuración CI contiene una matriz Moodle 5.0/5.1 con MySQL y PostgreSQL. Debe pasar antes de anunciar esas combinaciones como verificadas.

Publica una GitHub Release beta y adjunta el ZIP instalable. Las versiones estables posteriores deben llevar su propio incremento en `version.php`, changelog y etiqueta. Conserva el nombre del componente durante las actualizaciones.
