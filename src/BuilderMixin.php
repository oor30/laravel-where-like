<?php

namespace Kazuki\LaravelWhereLike;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class BuilderMixin
{
    public function whereLike()
    {
        /**
         * あいまい検索
         *
         * @param  array|string  $attributes 検索対象とするカラム名。リレーション先のカラム名を指定する場合は、テーブル名とカラム名を`.`で連結する（複数リレーション連結可能）。
         * @param  array|string  $keywords 検索値。配列の場合は、各検索値をor条件で連結する。
         * @param  int  $position `-1` => 前方一致 `1` => 後方一致 `0` => 部分一致（デフォルトは`0`）
         * @param  string  $boolean `and`か`or`を指定。デフォルトは`and`。
         * @return \Illuminate\Database\Eloquent\Builder
         */
        return function (array|string $attributes, array|string $keywords, int $position = 0, $boolean = 'and') {
            // 検索値が配列の場合、再帰呼び出し。
            // 検索値同士は、or検索で連結する。
            if (is_array($keywords)) {
                /** @var Builder $this */
                return $this->where(function (Builder $query) use ($attributes, $keywords, $position) {
                    foreach ($keywords as $keyword) {
                        $query->whereLike($attributes, $keyword, $position, 'or');
                    }
                }, boolean: $boolean);  // and・or 条件
            }

            // SQLインジェクション対策
            $keywords = addcslashes($keywords, '\_%');

            // 検索位置
            $condition = [
                1 => "{$keywords}%",
                -1 => "%{$keywords}",
            ][$position] ?? "%{$keywords}%";

            // カラム名が単体（文字列）で与えられた場合、配列に変換
            if (! is_array($attributes)) {
                $attributes = [$attributes];
            }

            /** @var Builder $this */
            return $this->where(function (Builder $query) use ($attributes, $condition) {
                foreach (Arr::wrap($attributes) as $attribute) {
                    $query->when(
                        // $attributeが、'.'（リレーション）を含むか確認
                        ! ($attribute instanceof \Illuminate\Contracts\Database\Query\Expression) &&
                        str_contains((string) $attribute, '.'),
                        // '.'（リレーション）を含む場合
                        function (Builder $query) use ($attribute, $condition) {
                            // $attributeを'.'でリレーションとカラム名に分割
                            $attrs = explode('.', (string) $attribute);
                            $relatedAttribute = array_pop($attrs);
                            $relation = implode('.', $attrs);
                            $query->orWhereRelation($relation, $relatedAttribute, 'LIKE', $condition);
                        },
                        // '.'（リレーション）を含まない場合
                        function (Builder $query) use ($attribute, $condition) {
                            $query->orWhere($attribute, 'LIKE', $condition);
                        }
                    );
                }
            }, boolean: $boolean);  // and・or 条件
        };
    }

    public function orWhereLike()
    {
        /**
         * あいまい検索（or）
         *
         * @param  array|string  $attributes 検索対象とするカラム名。リレーション先のカラム名を指定する場合は、テーブル名とカラム名を`.`で連結する。
         * @param  array|string  $keywords 検索値
         * @param  int  $position `-1` => 前方一致 `1` => 後方一致 `0` => 部分一致（デフォルトは`0`）
         * @return \Illuminate\Database\Eloquent\Builder
         */
        return function (array|string $attributes, array|string $keywords, int $position = 0) {
            /** @var Builder $this */
            return $this->whereLike($attributes, $keywords, $position, 'or');
        };
    }
}
