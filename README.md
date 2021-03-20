![](https://upload.wikimedia.org/wikipedia/commons/0/08/Shortdescription-icon-ltr.svg)
# ShortDescription
![](https://github.com/StarCitizenTools/mediawiki-extensions-ShortDescription/workflows/MediaWiki%20CI/badge.svg)

The ShortDescription extension allows wikis to add short descriptions to wikitext pages, simliar to the implementation on [Wikipedia](https://en.wikipedia.org/wiki/Wikipedia:Short_description) and Wikibase. On top of that, it replaces the site tagline with short description on pages with short description.

[Extension:ShortDescription on MediaWiki](https://www.mediawiki.org/wiki/Extension:ShortDescription).

## Features
* Define short description on the page with the magic word `{{SHORTDESC}}`, same as the implementation on Wikipedia
* Retrieve short description on any wiki pages with the magic word `{{GETSHORTDESC}}`
* Add short description underneath the page title on most skins, if the skin supports site tagline (`#siteSub`)
  * Note that the short description with replace the default site tagline message on pages with short description
  * Does not apply to [Skin:Citizen](https://www.mediawiki.org/wiki/Skin:Citizen) and [Skin:Minerva Neue](https://www.mediawiki.org/wiki/Skin:Minerva_Neue), as they have native support
* Allow short description to be accessed through the Action API
* Provide description to the REST API search endpoint
* Add short description to page information (`&action=info`)
* Provide description for extensions such as MobileFrontend, RelatedArticles

## Requirements
* [MediaWiki](https://www.mediawiki.org) 1.35 or later

## Installation
You can get the extension via Git (specifying ShortDescription as the destination directory):

    git clone https://github.com/StarCitizenTools/mediawiki-extensions-ShortDescription.git ShortDescription

Or [download it as zip archive](https://github.com/StarCitizenTools/mediawiki-extensions-ShortDescription/archive/master.zip).

In either case, the "ShortDescription" extension should end up in the "extensions" directory 
of your MediaWiki installation. If you got the zip archive, you will need to put it 
into a directory called ShortDescription.

## Configurations
**The extension works out of the box without any configurations.** 
The config flags allow more customization on the specific features in the extension. 

Name | Description | Values | Default
:--- | :--- | :--- | :---
`$wgShortDescriptionEnableTagline` | Enables short descritption in site tagline | `true` - enable; `false` - disable | `true`
`$ShortDescriptionExtendOpenSearchXml` | Provide short description to the Opensearch API module | `true` - enable; `false` - disable | `false`

## Usage
### Add short description 
To add `Bacon ipsum dolor amet turkey` as short description, simply add `{{SHORTDESC:Bacon ipsum dolor amet turkey}}` on the page.

### Retrive short description on wikipage
To retrive the short description on the page `Bacon`, simply add `{{GETSHORTDESC:Bacon}}` on the page. If you are retrieving the short description on the same page (e.g. getting the short description of `Bacon` on the `Bacon` page), simply add `{{GETSHORTDESC:}}`.

### Retrieve short description through Action API
The short description can be called through the `description` property in `query` action in the [Action API](https://www.mediawiki.org/wiki/API:Main_page) (e.g.`api.php?action=query&prop=description`). It is also accessible through the `shortdesc` property inside `pageprops`.

### Retrieve short description through REST API
The short description can be accessed through the `description` property in the [search endpoint](https://www.mediawiki.org/wiki/API:REST_API/Reference) in the [REST API](https://www.mediawiki.org/wiki/API:REST_API).
