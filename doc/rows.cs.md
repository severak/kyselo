# rows

Cílem knihovny `severak\database\rows` je usnadnit prodvádění běžných SQL dotazů a zkrátit jejich zápis oproti použití čistého `PDO`. Např. získání jednoho článku se zkrátí z původního zápisu:

```
$stmt = $pdo->prepare('SELECT * FROM articles WHERE id=?');
$stmt->execute([192]);
$article = $stmt->fetch(PDO::);
```

na nový:

```
$article = $rows->one('articles', 192);
```

Knihovna není určena lidem co hledají [ORM](https://en.wikipedia.org/wiki/Object-relational_mapping) nebo lpí na čistém objektovém návrhu. Nic z toho nemám.

## požadavky

- PHP 5.6 a výše (vyvíjeno na 7.0 ale bez problémů běží na 5.6)
- PDO
- SQlite3 (ale teoreticky by mělo běžet i nad jinými databázemi, žádné SQLite-specific SQL knihovna negeneruje)

## příklady použití

### získávání jednoho záznamu

```
// podle ID
$article = $rows->one('articles', 192);

// podle složeného klíče
$thatArticle = $rows->one('articles', ['author_name'=>'Ferdinand Peroutka', 'title'=>'Hitler je gentleman']);

// podle pořadí
$lastArticle = $rows->one('articles', [], ['time'=>'DESC']);
```

### získávání více záznamů

```
// todo
```

### počítání záznamů

```
$rows->count('');
```

## metody

- `one($table, $where=[], $order=[])`
- `more($table, $where=[], $order=[], $limit=30)`
- `count($table, $where=[])`
- `page($table, $where=[], $order=[], $page=1, $perPage=30)` - celkový počet stránek se (po dotazu) určuje pomocí `$rows->pages`
- `with($table, $from='id', $to='id', $where=[])`
- `insert($table, $data)`
- `update($table, $data, $where)`
- `delete($table, $where)`
- `query($sql, $params)`

Všechny metody vyhazují `PDOException` nebo `severak\database\usageException`.

Prázdný výsledek není považován za chybu.