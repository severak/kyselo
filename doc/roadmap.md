# Kyselo implementation plan

Sorry - only in Czech for now.

## kýžená funkcionalita

- registrace ✔
- login/logout ✔
- nastavení blogu (titulek, popis, avatar) ✔
- nastavení barviček blogu
- postování ⌛
- předvyplnění skrz bookmarklet
- repostování {ajax}
- editace postů
- následování jiných blogů {ajax} ✔
- feed sledovaných - `/blog/friends` ✔
- feed fof - `/blog/fof`
- feed všech příspěvků (*tady to žije*) - `/all`
- RSS podoba feedů
- vstoupení do skupiny
- posílání postů do skupiny {ajax}
- administrace skupiny
- skrývání NSFW postů skrz CSS
- skrývání NSFW blogů celkově
- automatický mirror ze RSS (Soupu) v crontabu
- výpisy členů skupin a přátel v hlavičce blogu
- adresář skupin/blogů
- [filtrování blogů](http://didyouknow.soup.io/post/481207241/You-can-easily-filter-your-Soup-and)
- statický export
- soukromé zprávy
- superadministrace

## technika

- Flight::flash() ✔
- Flight::requireLogin() ✔
- Flight::config($property) ✔
- Flight::user($property) ✔

## způsoby řešení

- data do SQlite ✔, později volitelně MySQL
- obrázky na FS, později S3 nebo něco podobného
- komunikace JS frontend «» backend - JSON-RPC
- co není nutné v JS, tak v PHP
- optimalizovat na desktop brambory, později pro mobily
- média se spouští až po kliku (jak to udělat pro gify?)
- kešovat výpisy + detaily (http://flightphp.com/learn/#httpcaching)
- [zabezpečit](http://flourishlib.com/docs/Security) až nakonec + [útoky breach, heist](https://www.fg.cz/cs/deje-se/prolomeni-sifrovaneho-protokolu-https-10930)

## todo

- CSRF ochrana
- refaktor follow tlačítek (na AJAX s fallbackem na POST)
- nové typy obsahu: nahrávka, snippet a galerie
- postování citací bookmarkletem
- přestylovat hlavičku blogu
- nějaké základní mobilní CSS