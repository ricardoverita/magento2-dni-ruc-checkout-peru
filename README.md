# Magento 2 DNI/RUC Checkout Perú

Módulo independiente para Magento Open Source y Adobe Commerce que incorpora la selección de Boleta o Factura en el checkout peruano y persiste los datos tributarios del comprobante.

## Características

- Selector de comprobante: Boleta o Factura.
- Validación de DNI peruano de 8 dígitos para Boleta.
- Validación de RUC peruano de 11 dígitos, prefijos permitidos y dígito verificador para Factura.
- Campos de razón social y dirección fiscal para Factura.
- Validación en frontend y backend.
- Soporte para clientes invitados y registrados.
- Persistencia de datos en `quote` y `sales_order`.
- Visualización de los datos del comprobante en el pedido administrativo.
- Textos traducibles en `es_PE` y `en_US`.

## Compatibilidad

- Magento Open Source 2.4.x.
- Adobe Commerce 2.4.x.
- Checkout estándar Luma.
- Guest checkout y customer checkout.

## Instalación rápida

Desde la raíz de una instalación Magento, copiar el contenido de este repositorio conservando la estructura `app/code/Vera/DniRucCheckout` y ejecutar:

```bash
bin/magento module:enable Vera_DniRucCheckout
bin/magento setup:upgrade
bin/magento cache:flush
```

En modo producción, ejecutar también los comandos de compilación y despliegue de contenido estático requeridos por el entorno.

## Documentación completa

La documentación específica del módulo, incluyendo la configuración administrativa, está disponible en [app/code/Vera/DniRucCheckout/README.md](app/code/Vera/DniRucCheckout/README.md).

## Licencia y contacto

Este proyecto se distribuye bajo la [licencia MIT](https://github.com/ricardoverita/magento2-dni-ruc-checkout-peru/blob/main/LICENSE).

GitHub: [github.com/ricardoverita](https://github.com/ricardoverita)

LinkedIn: [linkedin.com/in/ricardo-vera](https://www.linkedin.com/in/ricardo-vera/)
