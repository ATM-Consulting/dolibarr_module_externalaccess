# Change Log
All notable changes to this project will be documented in this file.

# RELEASE 1.2

## NEW

### NEW : Display and design improuvements
- Fullcalendar lib
- Iframe support
- Add new event messages style
- Add smart searchable select input support (16/06/2019)
- WYSIWYG editor
- Mobile design improuvement by adding manifest

### NEW : Shipping support

### NEW : Project support
Allow external user to show project support

### NEW : Langs support
- Create German externalaccess.lang
- Create spanish externalaccess.lang

### NEW : Ticket support
- Ajout de la fiche d'un ticket au portail externe avec affichage des infos principales + timeline des messages du ticket
- Add ticket file list on ticket message
  Reste à permettre de cliquer sur les liens pour les voir mais au moins on voit la liste des fichiers que l'on a envoyé

### NEW : Footer link behavior
Dans le footer, sur l'icône de contact, si le mail de contact n'est pas une adresse email alors conversion en lien std.
Permet par exemple de mettre un lien vers la page de contact du site vitrine de la société.

### NEW : Redirection on login to custom or asked page

### NEW Add compatibility with Multicompany Module
Fix an issue that show all documents available in DOLIBARR when Multicompany module activated.
Now, it will show only the documents created by the Portal owner company.

## FIX

### FIX : Divers
- FIX : Modification du comportement d'affichage des éléments du footer si pas de téléphone ou pas de mail de contact, il ne sont pas affichés
- FIX : changement de librairie et autre dom déprécié
- FIX : Add good practice for css : déplacement de style css vers un fichier dédié.

### FIX : tables ordering (20/10/2020)
The tables were ordered by ref asc by default.
The sql requests were ordered by date DESC which is logical but not implemented on list rendering.
That's done now.

### FIX : Trigger class doesn't extend DolibarrTriggers (30/09/2020)
- Change the subtotal module trigger class definition to avoid error message from Dolibarr
- Rename `run_trigger` to `runTrigger`
- Remove the declaration of the `$db` field if present (already declared as `protected` in the parent class)

### FIX : Ticket : Fix private msg, msg sort order, secure message access
- Sécurité : id ticket est modifiable dans l'URL
- Wysiwyg : se caler sur l'option du module standard Dolibarr
- Messages : ne pas afficher les messages privés si utilisateur externe
- UI : ordre d'affichage des messages
- UI : ne pas afficher "Nouveau message" sur chaque message
