<?php

namespace App\Services\LPadmin;

use App\Models\LPadmin\Dictionary;
use App\Models\LPadmin\DictionaryItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DictionaryService
{
    /**
     * 获取字典数据（带缓存）
     */
    public function getDictData(string $name, bool $enabledOnly = true): array
    {
        $cacheKey = 'lpadmin_dict_' . $name . ($enabledOnly ? '_enabled' : '_all');
        
        return Cache::remember($cacheKey, 3600, function () use ($name, $enabledOnly) {
            $query = Dictionary::with(['items' => function ($query) use ($enabledOnly) {
                if ($enabledOnly) {
                    $query->enabled();
                }
                $query->ordered();
            }])->where('name', $name);

            if ($enabledOnly) {
                $query->enabled();
            }

            $dictionary = $query->first();

            if (!$dictionary) {
                return [];
            }

            return $dictionary->items->map(function ($item) {
                return [
                    'label' => $item->label,
                    'value' => $item->value,
                    'color' => $item->color,
                    'description' => $item->description,
                ];
            })->toArray();
        });
    }

    /**
     * 获取字典选项（用于表单）
     */
    public function getDictOptions(string $name, bool $enabledOnly = true): array
    {
        $data = $this->getDictData($name, $enabledOnly);
        
        $options = [];
        foreach ($data as $item) {
            $options[$item['value']] = $item['label'];
        }
        
        return $options;
    }

    /**
     * 根据值获取标签
     */
    public function getDictLabel(string $name, string $value): string
    {
        $data = $this->getDictData($name);
        
        foreach ($data as $item) {
            if ($item['value'] === $value) {
                return $item['label'];
            }
        }
        
        return $value;
    }

    /**
     * 根据值获取颜色
     */
    public function getDictColor(string $name, string $value): string
    {
        $data = $this->getDictData($name);
        
        foreach ($data as $item) {
            if ($item['value'] === $value) {
                return $item['color'] ?: 'gray';
            }
        }
        
        return 'gray';
    }

    /**
     * 清除字典缓存
     */
    public function clearDictCache(string $name = null): void
    {
        if ($name) {
            Cache::forget('lpadmin_dict_' . $name . '_enabled');
            Cache::forget('lpadmin_dict_' . $name . '_all');
        } else {
            // 清除所有字典缓存
            $dictionaries = Dictionary::pluck('name');
            foreach ($dictionaries as $dictName) {
                Cache::forget('lpadmin_dict_' . $dictName . '_enabled');
                Cache::forget('lpadmin_dict_' . $dictName . '_all');
            }
        }
    }

    /**
     * 创建字典
     */
    public function createDictionary(array $data): Dictionary
    {
        return DB::transaction(function () use ($data) {
            $dictionary = Dictionary::create($data);
            
            // 清除相关缓存
            $this->clearDictCache();
            
            return $dictionary;
        });
    }

    /**
     * 更新字典
     */
    public function updateDictionary(Dictionary $dictionary, array $data): Dictionary
    {
        return DB::transaction(function () use ($dictionary, $data) {
            $dictionary->update($data);
            
            // 清除相关缓存
            $this->clearDictCache($dictionary->name);
            
            return $dictionary;
        });
    }

    /**
     * 删除字典
     */
    public function deleteDictionary(Dictionary $dictionary): bool
    {
        return DB::transaction(function () use ($dictionary) {
            // 删除字典项
            $dictionary->items()->delete();
            
            // 删除字典
            $result = $dictionary->delete();
            
            // 清除相关缓存
            $this->clearDictCache($dictionary->name);
            
            return $result;
        });
    }

    /**
     * 批量删除字典
     */
    public function batchDeleteDictionaries(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            // 获取要删除的字典名称
            $dictNames = Dictionary::whereIn('id', $ids)->pluck('name');
            
            // 删除字典项
            DictionaryItem::whereIn('dictionary_id', $ids)->delete();
            
            // 删除字典
            $count = Dictionary::whereIn('id', $ids)->delete();
            
            // 清除相关缓存
            foreach ($dictNames as $name) {
                $this->clearDictCache($name);
            }
            
            return $count;
        });
    }

    /**
     * 创建字典项
     */
    public function createDictionaryItem(Dictionary $dictionary, array $data): DictionaryItem
    {
        return DB::transaction(function () use ($dictionary, $data) {
            $item = $dictionary->items()->create($data);
            
            // 清除相关缓存
            $this->clearDictCache($dictionary->name);
            
            return $item;
        });
    }

    /**
     * 更新字典项
     */
    public function updateDictionaryItem(DictionaryItem $item, array $data): DictionaryItem
    {
        return DB::transaction(function () use ($item, $data) {
            $item->update($data);
            
            // 清除相关缓存
            $this->clearDictCache($item->dictionary->name);
            
            return $item;
        });
    }

    /**
     * 删除字典项
     */
    public function deleteDictionaryItem(DictionaryItem $item): bool
    {
        return DB::transaction(function () use ($item) {
            $dictionaryName = $item->dictionary->name;
            
            $result = $item->delete();
            
            // 清除相关缓存
            $this->clearDictCache($dictionaryName);
            
            return $result;
        });
    }

    /**
     * 批量删除字典项
     */
    public function batchDeleteDictionaryItems(Dictionary $dictionary, array $ids): int
    {
        return DB::transaction(function () use ($dictionary, $ids) {
            $count = $dictionary->items()->whereIn('id', $ids)->delete();
            
            // 清除相关缓存
            $this->clearDictCache($dictionary->name);
            
            return $count;
        });
    }

    /**
     * 批量更新字典项排序
     */
    public function batchSortDictionaryItems(Dictionary $dictionary, array $items): bool
    {
        return DB::transaction(function () use ($dictionary, $items) {
            foreach ($items as $item) {
                $dictionary->items()
                          ->where('id', $item['id'])
                          ->update(['sort' => $item['sort']]);
            }
            
            // 清除相关缓存
            $this->clearDictCache($dictionary->name);
            
            return true;
        });
    }

    /**
     * 获取字典统计信息
     */
    public function getStatistics(): array
    {
        return [
            'total_dictionaries' => Dictionary::count(),
            'enabled_dictionaries' => Dictionary::enabled()->count(),
            'total_items' => DictionaryItem::count(),
            'enabled_items' => DictionaryItem::enabled()->count(),
            'type_stats' => Dictionary::select('type')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('type')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->type => $item->count];
                }),
        ];
    }

    /**
     * 导出字典数据
     */
    public function exportDictionary(Dictionary $dictionary): array
    {
        return [
            'dictionary' => $dictionary->toArray(),
            'items' => $dictionary->items()->ordered()->get()->toArray(),
        ];
    }

    /**
     * 导入字典数据
     */
    public function importDictionary(array $data): Dictionary
    {
        return DB::transaction(function () use ($data) {
            // 创建或更新字典
            $dictionary = Dictionary::updateOrCreate(
                ['name' => $data['dictionary']['name']],
                $data['dictionary']
            );
            
            // 删除现有字典项
            $dictionary->items()->delete();
            
            // 导入字典项
            if (!empty($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    unset($itemData['id'], $itemData['dictionary_id']);
                    $dictionary->items()->create($itemData);
                }
            }
            
            // 清除相关缓存
            $this->clearDictCache($dictionary->name);
            
            return $dictionary;
        });
    }
}
