# Vera_DniRucCheckout

Módulo independiente para Magento Open Source y Adobe Commerce 2.4 que agrega los datos fiscales peruanos al checkout Luma.

## Características

- Selector Boleta/Factura en el paso de pago.
- DNI de 8 dígitos para Boleta.
- RUC de 11 dígitos, prefijo peruano y dígito verificador para Factura.
- Razón social y dirección fiscal con máximo de 255 caracteres.
- Validación Knockout/frontend y validación PHP/backend.
- Persistencia en `quote` y `sales_order` mediante declarative schema.
- Compatibilidad con invitados y clientes registrados.
- Bloque administrativo de solo lectura en la vista del pedido.
- Traducciones `es_PE` y `en_US`.

## Instalación en una instancia Magento

Copiar esta carpeta como `app/code/Vera/DniRucCheckout` y ejecutar:

```bash
bin/magento module:enable Vera_DniRucCheckout
bin/magento setup:upgrade
bin/magento cache:flush
```

En modo producción también se debe ejecutar la compilación y el despliegue de contenido estático correspondientes al entorno.

La configuración está en `Stores > Configuration > Sales > Vera DNI/RUC Checkout`.
