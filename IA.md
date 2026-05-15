# Uso de IA en este proyecto

## 1. Herramientas utilizadas

| Herramienta | Versión / Modelo | Modo de uso | Aprox. % del trabajo |
|Claude|Sonnet 4.5|Desktop|60%-70%|


## 2. Configuración del proyecto

### CLAUDE.md / AGENTS.md
¿Tienes archivo de instrucciones a nivel proyecto? Pega aquí su contenido o
linka al fichero del repo. Si no tienes, escribe "ninguno" y explica por qué.

Ninguno, uso la IA al principio de los proyecto como planificador, le doy un contexto general de lo que hay que hacer y luego voy especificandole 1 a 1 cada punto y como se va a desarrollar, ya que cada proyecto es distinto y la IA sin una explicación detallada no funciona de manera correcta

### settings.json u otra configuración equivalente
¿Cambiaste permisos, modelo activo, herramientas habilitadas/bloqueadas?
Adjunta el archivo al repo y referencia aquí su ruta.

/.vscode/settings.json

## 3. Skills personalizadas

Si usaste skills (propias o de terceros), lístalas:

Ninguna

## 4. Slash commands personalizados

Si tienes comandos custom (`/revisa-modulo`, `/genera-hook`...), lístalos
de la misma forma. Deben estar en `.claude/commands/` o equivalente.

no use ningun comando custom

## 5. Sub-agentes invocados

¿Usaste Task tool, Plan Mode, sub-agentes? Indica para qué los usaste y si
guardaste sus definiciones en el repo (`.claude/agents/`).

Claude en desktop no tiene modos, pero el uso correcto seria primero en "Plan Mode" y luego de que entienda todo el contexto lo cambiaria a modo "Agent Mode"

## 6. MCPs (Model Context Protocol)

¿Conectaste algún MCP server durante el trabajo?

| MCP | Para qué lo usaste | ¿Qué te aportó? |
|---|---|---|
| (ej. filesystem) | (lectura del repo) | (navegación más rápida) |
| (ej. github) | — | (no lo usé) |
| (ej. context7) | (docs de PrestaShop) | (evitó alucinaciones en hooks) |

Si no usaste ninguno, dilo y explica si lo habrías usado con más tiempo.

No use ninguno por el tiempo y porque es un proyecto pequeño dentro de lo que cabe, no tenia mucha necesisdad de hacer que la IA tuviera todo el contexto de todo el proyecto sino unicamente de funciones, aunque eso hubiese hecho el trabajo más fácil

## 7. Prompts importantes

Lista los 5-10 prompts más relevantes (no todos, los que dieron forma al
proyecto). Por cada uno:

### Prompt 1
- **Herramienta:** (Claude Code)
- **Prompt:** "Analiza este documento de prueba tecnica que tengo que realizar"
- **Qué generó (resumen):** La IA leyo el documento entero y hizo un resumen de lo que decia
- **Qué hice con el output:** Acepte su respuesta he hice que razonara más ciertos puntos

### Prompt 2
- **Herramienta:** (Claude Code)
- **Prompt:** "De tu analisis obten los requisitos tecnicos que necesitaremos"
- **Qué generó (resumen):** La IA estrajo los requisitos tecnicos que se exigian en el documento (versiones de prestashop y php)
- **Qué hice con el output:** Acepte su respuesta he hice que hiciera un plan para instalarlos

### Prompt 3
- **Herramienta:** (Claude Code)
- **Prompt:** "De tu analisis realiza un guión de un paso a paso para trazar el plan de desarrollo de la aplicación"
- **Qué generó (resumen):** La IA extrajo los puntos he hizo un guión con los pasos que se tenian que hacer para empezar el desarrollo
- **Qué hice con el output:** Modifique ciertos pasos ya que saltaba mucho entre ficheros y no estaba muy bien estructurado (ejemplo: la ia proponia de hacer como paso final el fichero docker-compose incapacitando la posibilidad de probar el proyecto mientras se desarrollaba)

### Prompt 4
- **Herramienta:** Claude Code
- **Prompt:** "Genera el esquema de base de datos para la relación muchos a muchos entre etiquetas y productos."
- **Qué generó (resumen):** SQL de creación de tablas badge, badge_lang y la tabla pivote badge_product.
- **Qué hice con el output:** Verifiqué que incluyera los índices necesarios para que las consultas en el listado de productos fueran eficientes.

### Prompt 5
- **Herramienta:** Claude Code
- **Prompt:** "Genera un comando mkdir para crear todos los archvios y la estructura del proyecto"
- **Qué generó (resumen):** me genero el comando mkdir para la creación de las carpetas del proyecto
- **Qué hice con el output:** Probe el comando y revise que estuvieran todas las carpetas creadas para ahorrarme el crearlas 1 a 1

## 8. Errores de la IA que detecté

Lista bugs, invenciones, malas prácticas o riesgos de seguridad que la IA
introdujo y tú corregiste. Por cada uno:

- **Qué generó la IA (mal):**
- **Por qué estaba mal:**
- **Cómo lo corregiste:**

Si dices "ninguno", piénsalo dos veces. En PrestaShop 1.7 la IA suele
equivocarse en cosas concretas. Si realmente no detectaste nada, dilo y
reflexiona sobre qué podría haber pasado.

- **Qué generó la IA (mal):** Acceso a propiedades de objeto en hooks de listado ($product->id).
- **Por qué estaba mal:** En PrestaShop 1.7.8, los hooks de listado suelen recibir arrays de datos, no objetos Product instanciados, lo que provocaba errores de ejecución (PHP Fatal Error/Notice).
- **Cómo lo corregiste:** Debido a que es primera vez que usaba prestashop use a la propia IA para traducir el error y buscar el fallo dentro de los archivos del proyecto, una vez analizado el error hice que la IA me sugiriera propuestas de soluciones para escoger las que más se adaptara y fuera mas factible

- **Qué generó la IA (mal):**Registro de estilos CSS con rutas relativas que no resolvían correctamente en entornos Docker con URLs personalizadas.
- **Por qué estaba mal:**Al usar puertos específicos (8080) y carpetas de módulos, las rutas relativas pueden romperse si no se usa el helper nativo.
- **Cómo lo corregiste:**Forcé el uso de $this->_path en el método addCSS dentro de productbadges.php.

- **Qué generó la IA (mal):** Falta en el guión sobre la lista de productos que afectarian los badges
- **Por qué estaba mal:** Al crearse el Badge faltaba la lista de los productos los cuales llevarian dicho Badge
- **Cómo lo corregiste:** Añadi un bloque extra en el formulario donde esta la lista de los productos para seleccionarlos 1 a 1 (posible mejora: añadir un boton para seleccionar todos o deseleccionar todos)

- **Qué generó la IA (mal):** Errores en el archivo front.css
- **Por qué estaba mal:** El badge lo colocaba en una posición incorrecta o directamente no aparecia
- **Cómo lo corregiste:** Revise el codigo HTML generado por la página para concretar si existia el badge y en donde estaba colocado, tuve que especificarle como colocarlo y en donde, además de decirle los estilos que se necesitaban para que estuviera en la posicion correcta (align: flex-end , position: absolute, etc)

- **Qué generó la IA (mal):** Falta badge en la pestaña Home
- **Por qué estaba mal:** Todos los badges estan colocados de manera correcta en los productos menos en el home, puesto que alli no aparecen
- **Cómo lo corregiste:** Este problema no he logrado solucionarlo, no puede hacer que el badge apareciera en los productos del Home

## 9. Partes que NO usé IA

Indica qué partes hiciste totalmente a mano y por qué decidiste no usar IA
en ellas.

El proyecto en su gran mayoria esta hecho con IA debido a que no conocia PrestaShop y fue mi primera vez usandolo, desconocia muchas de sus funcionalidades y maneras de trabajar, estuve consultado a la IA en su gran parte y el codigo que yo hacia manualmente era con muchas consultas a la IA sobre el funcionamiento de ciertas funciones y sus comportamientos, estuve muy al pendiente de todo el codigo que me daba la IA y siempre lo probaba antes de validarselo como bueno para minimizar los fallos

## 10. Reflexión final

- ¿Qué te ahorró la IA en este ejercicio?
R: La IA como herramienta es muy potente y ayuda a gestionar el tiempo de una mejor manera, el hecho de realizar 2 analisis del proyecto (el mio propio y el de la IA) hace que se puedan cubrir todos los puntos de una mejor manera ya que se abordan de 2 maneras distintas, pudiendo mejorar el rendimiento de desarrollo. Además de ahorrar el uso excesivo de la documentación de PrestaShop y de busquedas en internet de errores, ya que la IA me ayudo mucho al analisis de los errores para buscar soluciones

- ¿En qué te entorpeció o te llevó por mal camino?
Al no tener una buena nocion de espacio y perspectivas la IA entorpese mucho en el diseño, para colocar los badges bien dentro de la imagen la IA generaba archivos css los cuales pese a cambios que hacia no cambiaba la posicion de los badges, tuve que enseñarle con el HTML de la página dentro de que etiqueta se creaba el Badge para que se pudiera ubicar, pese a mis intentos tuve que realizar ciertos cambios a mano ya que no era capaz de realiazarlo de la manera que yo queria.

- ¿Qué cambiarías de tu flujo con IA si lo repitieras?
Siento que mi flujo de trabajo con IA es bastante adecuado puesto a que lo he desarrollado despues de muchos proyectos con su ayuda, el hacer que la IA analice y tenga un contexto general del proyecto para luego hacer que entienda todos los puntos de manera especifica ayuda a que no genere codigo innecesario o mal hecho, puesto que ya sabe cuales son los ficheros que se necesitan, los archivos y el flujo de trabajo que tendra el proyecto. Cambiaria el hecho de empezar el proyecto sin investigar antes lo que es PrestaShop y darle indicaciones a la IA en cuanto a diseño puesto a lo dicho antes, le cuesta ubicarse en esos aspectos