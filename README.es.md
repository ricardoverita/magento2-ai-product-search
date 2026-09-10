# SearchAI para Magento 2

SearchAI es un chatbot gratuito y de código abierto para descubrir productos en Magento Open Source y Adobe Commerce. El cliente pregunta en lenguaje natural; Magento recupera pocos candidatos; un proveedor de IA explica o recomienda únicamente esos productos; Magento valida los IDs y renderiza tarjetas con datos actuales.

Creado por Ricardo Vera — [VeraDesarrollo.com](https://veradesarrollo.com).

## Funciones

- Búsqueda nativa de Magento sin consultar directamente OpenSearch ni Elasticsearch.
- OpenAI gpt-5.6-luna, Anthropic claude-fable-5, Google Gemini gemini-3.8-flash y Custom/OpenAI-compatible.
- Configuración, moneda, idioma, URLs, catálogo y credenciales por ámbito de tienda.
- Ubicación en inicio, producto y categoría.
- Widget accesible, responsive, usable con teclado e historial sólo durante la sesión.
- Contexto compacto y saneado; la descripción completa está desactivada por defecto.
- Validación de candidatos: la IA no puede crear tarjetas para productos inexistentes.
- Credenciales cifradas, CSRF, límites de payload, rate limiting privado y errores seguros.
- Traducciones en inglés y español.
- Sin telemetry, analítica, backend SaaS ni llamadas a servidores de Vera.

## Requisitos

- Magento Open Source o Adobe Commerce 2.4.7 o superior; Magento 2.4.8 es el objetivo principal.
- PHP 8.2, 8.3 o 8.4.
- Motor de búsqueda de catálogo e inventario MSI operativos.
- Se recomienda HTTPS.
- Clave de un proveedor incorporado o gateway Custom accesible.

## Instalación con Composer

Cuando el paquete esté disponible en el repositorio Composer configurado:

~~~bash
composer require vera/module-search-ai
bin/magento module:enable Vera_SearchAI
bin/magento setup:upgrade
bin/magento cache:clean config layout block_html full_page
~~~

En modo producción ejecuta también el flujo normal de compilación de DI y contenido estático.

## Instalación manual

Copia el contenido del repositorio a app/code/Vera/SearchAI dentro de Magento y ejecuta los mismos comandos anteriores. No copies la carpeta padre Vera del entorno de desarrollo: composer.json, registration.php, etc, Model y view deben quedar directamente en app/code/Vera/SearchAI.

## Configuración

Ve a Tiendas > Configuración > Vera > SearchAI. Los valores admiten ámbitos global, sitio web y vista de tienda.

### General

Activa SearchAI, define título, bienvenida y placeholder, selecciona Homepage, páginas de producto o categoría, y limita el historial temporal. El valor predeterminado es seis mensajes guardados sólo en sessionStorage.

El widget se oculta cuando está desactivado o falta la configuración mínima del proveedor.

### Proveedores

- OpenAI usa Responses API con gpt-5.6-luna.
- Anthropic usa Messages API con claude-fable-5.
- Google Gemini usa generateContent con gemini-3.8-flash.
- Custom usa Chat Completions compatible con OpenAI.

Magento cifra las claves y nunca las entrega al JavaScript. El uso de la API puede tener costo, facturado por el proveedor elegido; Vera y SearchAI no cobran ese consumo. El módulo es gratuito.

Custom permite URL base HTTP/HTTPS, ruta, modelo, clave opcional, nombre del encabezado y prefijo. Los valores predeterminados son /v1/chat/completions y Authorization: Bearer. Sirve para OpenRouter y gateways locales/autohospedados compatibles con el formato esperado.

### Catálogo

Candidate Products vale 8 y admite 3–20. La descripción corta está activa. Include Full Description in AI Context está desactivado. Al habilitarla se limita a 1.500 caracteres; la corta se limita a 500.

Los atributos adicionales se escriben separados por comas, por ejemplo brand,color,size,material. Sólo se incluyen atributos existentes, buscables o visibles en storefront. Coste y campos internos conocidos se bloquean. Los productos sin stock se excluyen por defecto.

### Seguridad y opciones avanzadas

Requests per Minute vale 10. Longitud de consulta, timeout y respuesta tienen límites. La guía adicional del sistema no sustituye las reglas internas que restringen la IA a candidatos de Magento.

## Rendimiento

SearchAI no sube ni indexa todo el catálogo en el LLM:

~~~text
Pregunta del cliente
  -> búsqueda Magento
  -> mejores candidatos
  -> contexto compacto
  -> interpretación de IA
  -> tarjetas validadas por Magento
~~~

Preferir descripción corta reduce tokens, latencia, tamaño, costo y ruido. La recuperación está limitada y las consultas de categorías/salabilidad se realizan por lote.

## Privacidad y seguridad

Con un proveedor externo, la pregunta, el historial reciente limitado y los datos compactos de candidatos viajan desde Magento a dicho proveedor. Revisa sus condiciones y política de privacidad.

SearchAI no crea una base de conversaciones ni registra mensajes por defecto. No envía telemetry a Vera, no incluye analítica oculta y no llama a servidores de Vera. El flujo es Magento store <-> selected AI provider.

El endpoint POST valida form_key. El texto de la IA nunca se inserta como HTML. Precio, imagen, URL, nombre, SKU, moneda, visibilidad y stock proceden de Magento. El rate limiter usa identificadores con hash y no almacena IP sin procesar.

## Solución de problemas

- No aparece: revisa activación, proveedor, ámbito, ubicación y limpia cachés.
- No disponible: comprueba clave, modelo, conectividad HTTPS, timeout y límites de cuenta.
- Sin resultados: revisa estado, visibilidad de búsqueda, asignación a website/store, índices y stock.
- Custom falla: revisa URL, ruta, modelo, encabezado y el campo choices[0].message.content.
- Cambios visuales: vuelve a desplegar contenido estático y limpia cachés.

## Pruebas

En una instalación de desarrollo Magento 2.4.7/2.4.8 con dependencias:

~~~bash
vendor/bin/phpunit -c app/code/Vera/SearchAI/phpunit.xml.dist
vendor/bin/phpcs --standard=app/code/Vera/SearchAI/phpcs.xml.dist app/code/Vera/SearchAI
find app/code/Vera/SearchAI -name '*.php' -print0 | xargs -0 -n1 php -l
~~~

Verifica module:status Vera_SearchAI, setup:upgrade, configuración Admin, ubicaciones, búsqueda real, cada proveedor, JSON inválido, claves ausentes, rate limiting e IDs inventados.

Este repositorio aislado no incluye Magento, base de datos, buscador, credenciales ni autenticación de Commerce Marketplace. Las pruebas de instalación e integración deben ejecutarse dentro de Magento.

## Contribuciones

Consulta CONTRIBUTING.md. Mantén proveedores en adaptadores, catálogo detrás de ProductRetrieverInterface y las tarjetas bajo autoridad de Magento.

## Licencia y contacto

MIT License. Copyright (c) 2026 Ricardo Vera.

- Sitio: [VeraDesarrollo.com](https://veradesarrollo.com)
- GitHub: [ricardoverita](https://github.com/ricardoverita)
- LinkedIn: [Ricardo Vera](https://www.linkedin.com/in/ricardo-vera/)
