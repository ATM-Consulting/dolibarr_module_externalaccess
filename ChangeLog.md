# Change Log
All notable changes to this project will be documented in this file.
___

## [Unreleased]

- NEW : Hooks for queries [2021-02-08]

## RELEASE 1.12 - 2021-02-26

- FIX : retour du tracking_url au lieu du tracking number [2021-04-15] v1.12.2
- NEW : Add Hook "listColumnField" in expedition list [2021-02-26]
- NEW : Add column linked object in shipping list with key format linked-(classname)-(fieldname)-(fieldtype) (conf EACCESS_LIST_ADDED_COLUMNS_SHIPPING) [2021-02-25]
- NEW : Add column "shipping method" in shipping list (conf EACCESS_LIST_ADDED_COLUMNS_SHIPPING) [2021-02-24]
- NEW : Add public file downloader [2021-02-03]
- NEW : Add get public ecm file to interface [2021-02-03]
- FIX : Ajout de l'option "tracking_number" dans la conf EACCESS_LIST_ADDED_COLUMNS_SHIPPING plutôt que la conf EACCESS_LIST_ADDED_COLUMNS  [2021-02-24]
- FIX : tracking_number and no tracking_url in shipping list [2021-02-24]
- FIX : download file [2021-02-03]


___
## RELEASE 1.6 - 2021-01-20
- NEW : Add product photo display possibility [2021-01-14]
- NEW : Add Hooks [2021-01-10]
- NEW : Add icon option for manifest [2021-01-10]
- NEW : Add option for simple home display [2021-01-10]
- NEW : Ajout du tri par défaut sur la date de création des tickets sur la liste des tickets [2020-12-15]
- NEW : Ajout colonne "Numéro de suivi" des expéditions pour cette liste uniquement (soumis à configuration)
- NEW : Add option to allow brand logo in menu and change login logo too [2020-12-15]
- NEW : Add context token system [2021-01-17]
- NEW : Add js dialog confirm for button possibility for developers [2021-01-17]
- NEW : getRootUrl can have an array for url parameters [2021-01-17]

- FIX : Missing translation keys [2020-12-15]
- FIX : Login page title [2020-12-16]
- FIX : Ticket file sharing (experimental conf) [2021-02-02]
___
## RELEASE 1.3 - 2020-12-01

### Added

- Ajout colonne Réf client sur toutes les listes (soumis à configuration)
- Ajout signature commercial associé au tiers de l'utilisateur externe

___
## RELEASE 1.2 - 2020-12-01

### Added

#### NEW : T2436 : Ajout de la colonne "Réf. client" sur toutes les listes du portail #34

### Changed

#### FIX : V12 compatibility
A partir de la v12 le paramètre check de la fonction GETPOST est alphanohtml par défaut ce qui fait que tous nos inputs ayant une balise html sont vidés.
Modification (et typage) de tous les paramètres check manquants.

#### FIX : Trigger class doesn't extend DolibarrTriggers
- Change the subtotal module trigger class definition to avoid error message from Dolibarr
- Rename `run_trigger` to `runTrigger`
- Remove the declaration of the `$db` field if present (already declared as `protected` in the parent class)


___
## RELEASE 1.1 - 2020-10-20

### Added

#### NEW : Display and design improuvements
- Fullcalendar lib
- Iframe support
- Add new event messages style
- Add smart searchable select input support (16/06/2019)
- WYSIWYG editor
- Mobile design improuvement by adding manifest

#### NEW : Shipping support

#### NEW : Project support
Allow external user to show project support

#### NEW : Langs support
- Create German externalaccess.lang
- Create spanish externalaccess.lang

#### NEW : Ticket support
- Ajout de la fiche d'un ticket au portail externe avec affichage des infos principales + timeline des messages du ticket
- Add ticket file list on ticket message
  Reste à permettre de cliquer sur les liens pour les voir mais au moins on voit la liste des fichiers que l'on a envoyé

#### NEW : Footer link behavior
Dans le footer, sur l'icône de contact, si le mail de contact n'est pas une adresse email alors conversion en lien std.
Permet par exemple de mettre un lien vers la page de contact du site vitrine de la société.

#### NEW : Redirection on login to custom or asked page

#### NEW Add compatibility with Multicompany Module
Fix an issue that show all documents available in DOLIBARR when Multicompany module activated.
Now, it will show only the documents created by the Portal owner company.

#### Changed

#### FIX : Divers
- FIX : Vérification de l'existance des fichier pour affichage du lien de téléchargement
- FIX : Modification du comportement d'affichage des éléments du footer si pas de téléphone ou pas de mail de contact, il ne sont pas affichés
- FIX : changement de librairie et autre dom déprécié
- FIX : Add good practice for css : déplacement de style css vers un fichier dédié.

#### FIX : tables ordering (20/10/2020)
The tables were ordered by ref asc by default.
The sql requests were ordered by date DESC which is logical but not implemented on list rendering.
That's done now.

#### FIX : Trigger class doesn't extend DolibarrTriggers (30/09/2020)
- Change the subtotal module trigger class definition to avoid error message from Dolibarr
- Rename `run_trigger` to `runTrigger`
- Remove the declaration of the `$db` field if present (already declared as `protected` in the parent class)

#### FIX : Ticket : Fix private msg, msg sort order, secure message access
- Sécurité : id ticket est modifiable dans l'URL
- Wysiwyg : se caler sur l'option du module standard Dolibarr
- Messages : ne pas afficher les messages privés si utilisateur externe
- UI : ordre d'affichage des messages
- UI : ne pas afficher "Nouveau message" sur chaque message
