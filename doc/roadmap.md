# Kyselo implementation plan

Sorry - only in Czech for now.

## kýžená funkcionalita

- registrace
- login/logout
- nastavení blogu (titulek, popis, avatar)
- nastavení barviček blogu
- postování + předvyplnění skrz bookmarklet
- repostování {ajax}
- editace postů
- následování jiných blogů {ajax}
- feed sledovaných - `/blog/friends`
- feed fof - `/blog/fof`
- feed všech příspěvků (tady to žije) - `/all`
- RSS podoba feedů
- vstoupení do skupiny
- posílání postů do skupiny {ajax}
- administrace skupiny
- skrývání NSFW postů skrz CSS
- skrývání NSFW blogů celkově
- automatický mirror ze RSS (Soupu) v crontabu
- výpisy členů skupin a přátel v hlavičce blogu
- adresář skupin/blogů

## způsoby řešení

- data do SQlite, později MySQL
- obrázky na FS, později S3?
- komunikace JS frontend «» backend - JSON-RPC
- co není nutné v JS, tak v PHP
- optimalizovat na desktop brambory, později pro mobily
- média se spouští až po kliku (jak to udělat pro gify?)
- [zabezpečit](http://flourishlib.com/docs/Security) až nakonec + [útoky breach, heist](https://www.fg.cz/cs/deje-se/prolomeni-sifrovaneho-protokolu-https-10930)