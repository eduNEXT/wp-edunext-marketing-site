Dirección del producto
======================

Este documento describe para donde va el producto. Cosas que tiene, y la motivación detrás de ellas. Cosas que le hacen falta.

Este documento es completamente privado y confidencial de edunext. Esta en español por conveniencia.



Features pendientes
-------------------

#. documentar

#. Publicar una versión que no tiene woocommerce ni eox_api
    Habría que cambiar los archivos por una versión -lite y borrar los correctos. Toda la lógica tiene que vivir en esos archivos
    Hacer que se vea un boton de comprar el plugin pro
#. Publicar como un Zip

#. Lint a todos los archivos
#. el render del menú debe ser opcionalmente client side
#. Hacer el fullfilment custom
#. Obtener el username de un custom field durante el proceso
#. hacer que si se usa el email del form o el custom_field o el username de wordpress sea configurable
#. soportar bundle_id



FMO:
====


#. Accion es per_product y el trigger tambien
    Los settings actuales son completamente globales
        hacer "default"
        hacer que el producto lo pueda sobre escribir
        documentar


Preguntas sobre el fulfillment
------------------------------

Deberiamos dejar opcional el hecho de que actualice la orden a Completed?

Cuestiones abiertas
    - que hay que hacer con el estado de la orden
    - no es viable hacer que cada curso defina cual es el action de woo commerce que se usa
