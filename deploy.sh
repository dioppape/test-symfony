#!/bin/bash
 echo "Deploiement\n";

 //doctrine migration
 echo "Création des tables\n";
 php app/console doctrine:migrations:migrate -n

 echo "Purge de la base de données\n";
 php app/console import:adt --truncate

 echo "Import des données\n";
 php app/console import:adt --force

