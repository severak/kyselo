# Kyselo implementation plan

Sorry - only in Czech for now.

## kýžená funkcionalita

- registrace
- login/logout
- nastavení blogu (titulek, popis, avatar)
- postování
- repostování {ajax}
- editace postů
- následování jiných blogů {ajax}
- feed sledovaných
- feed všech
- RSS podoba feedů
- vstoupení do skupiny
- posílání postů do skupiny {ajax}
- administrace skupiny
- skrývání NSFW postů skrz CSS
- skrývání NSFW blogů celkově

## způsoby řešení

- data do SQlite, později MySQL
- obrázky na FS, později S3?
- komunikace JS frontend «» backend - JSON-RPC