# laravel-where-like

Add `whereLike` query to laravel `Illuminate\Database\Eloquent\Builder`.

`Illuminate\Database\Eloquent\Builder`クラスに`whereLike`メソッドを追加します。

# install 

```sh
composer require kazuki/laravel-where-like
```

# Usage

```php
// Search for Ichiro working in Tokyo
$words = ['Ichiro', 'Tokyo'];
$comulns = ['name', 'kana', 'company.address'];
User::whereLike($columns, $words);
```

## options

- `$position`
    - -1: Forward Consistency (前方一致)
    - 1: Backward Consistency (後方一致)
    - 0: Partially Consistent (部分一致) (Default)
- `$boolean`
    - `'and'`: Default
    - `'or'`

```php
// Search for who working in Tokyo or Osaka
$words = ['Tokyo', 'Osaka'];
$comulns = ['company.address'];
User::whereLike($columns, $words, 0, 'or');
```
