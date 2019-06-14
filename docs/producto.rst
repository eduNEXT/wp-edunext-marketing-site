Dirección del producto
======================

Este documento describe para donde va el producto. Cosas que tiene, y la motivación detrás de ellas. Cosas que le hacen falta.

Este documento es completamente privado y confidencial de edunext. Esta en español por conveniencia.



Features pendientes
-------------------

#. Hacer una acción de encontrar los usuarios con un email sacado del un custom fields sacado del checkout form


#. documentar

#. Publicar una versión que no tiene woocommerce ni eox_api
    Habría que cambiar los archivos por una versión -lite y borrar los correctos. Toda la lógica tiene que vivir en esos archivos
    Hacer que se vea un boton de comprar el plugin pro
#. Publicar como un Zip

#. Lint a todos los archivos
#. el render del menú debe ser opcionalmente client side




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

El fulfillment deberia ser siempre el mismo y le metemos un default processing pipeline?

Cuestiones abiertas
    - que hay que hacer con el estado de la orden
    - no es viable hacer que cada curso defina cual es el action de woo commerce que se usa
    - si queremos que sea configurable por curso el fulfillment habria que:
        - usar siempre la misma funcion
        - que la función abra el order y obtenga los items
        - que para cada item la función llame a la función de fulfillment (la cual puede ser una serie de APIs)  < hay que hacerlo en todo caso


