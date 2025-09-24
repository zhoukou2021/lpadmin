<?php

namespace App\Traits;

/**
 * 时间格式化Trait
 */
trait TimeFormattable
{
    /**
     * 格式化创建时间
     */
    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : '';
    }

    /**
     * 格式化更新时间
     */
    public function getUpdatedAtFormattedAttribute(): string
    {
        return $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : '';
    }

    /**
     * 格式化删除时间
     */
    public function getDeletedAtFormattedAttribute(): string
    {
        return $this->deleted_at ? $this->deleted_at->format('Y-m-d H:i:s') : '';
    }

    /**
     * 格式化任意时间字段
     */
    public function formatTime($field, $default = ''): string
    {
        $value = $this->getAttribute($field);
        if ($value && method_exists($value, 'format')) {
            return $value->format('Y-m-d H:i:s');
        }
        return $default;
    }
}
