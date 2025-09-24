<?php

namespace App\Models\LPadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TimeFormattable;

/**
 * 用户模型
 *
 * @property int $id
 * @property string $username 用户名
 * @property string $password 密码
 * @property string $nickname 昵称
 * @property string $email 邮箱
 * @property string $phone 手机号
 * @property string $avatar 头像
 * @property int $gender 性别 0:未知 1:男 2:女
 * @property string $birthday 生日
 * @property int $status 状态 1:启用 0:禁用
 * @property string $last_login_at 最后登录时间
 * @property string $last_login_ip 最后登录IP
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property string $deleted_at 删除时间
 */
class User extends Model
{
    use HasFactory, SoftDeletes, TimeFormattable;

    /**
     * 数据表名称
     */
    protected $table = 'users';

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\LPadmin\UserFactory::new();
    }

    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'username',
        'password',
        'nickname',
        'email',
        'phone',
        'avatar',
        'gender',
        'birthday',
        'status',
        'remark',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * 隐藏的属性
     */
    protected $hidden = [
        'password',
    ];

    /**
     * 属性类型转换
     */
    protected $casts = [
        'gender' => 'integer',
        'status' => 'integer',
        'birthday' => 'date',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * 性别常量
     */
    const GENDER_UNKNOWN = 0;
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;

    /**
     * 状态常量
     */
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    /**
     * 性别标签
     */
    public static $genderLabels = [
        self::GENDER_UNKNOWN => '未知',
        self::GENDER_MALE => '男',
        self::GENDER_FEMALE => '女',
    ];

    /**
     * 状态标签
     */
    public static $statusLabels = [
        self::STATUS_DISABLED => '禁用',
        self::STATUS_ENABLED => '启用',
    ];

    /**
     * 获取性别标签
     */
    public function getGenderLabelAttribute(): string
    {
        return self::$genderLabels[$this->gender] ?? '未知';
    }

    /**
     * 获取状态标签
     */
    public function getStatusLabelAttribute(): string
    {
        return self::$statusLabels[$this->status] ?? '未知';
    }

    /**
     * 获取头像URL
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset($this->avatar);
        }
        return asset('static/admin/images/avatar.png');
    }

    /**
     * 获取年龄
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->birthday) {
            return null;
        }

        return $this->birthday->diffInYears(now());
    }

    /**
     * 检查是否启用
     */
    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLED;
    }

    /**
     * 检查是否为男性
     */
    public function isMale(): bool
    {
        return $this->gender === self::GENDER_MALE;
    }

    /**
     * 检查是否为女性
     */
    public function isFemale(): bool
    {
        return $this->gender === self::GENDER_FEMALE;
    }

    /**
     * 启用用户
     */
    public function enable(): bool
    {
        return $this->update(['status' => self::STATUS_ENABLED]);
    }

    /**
     * 禁用用户
     */
    public function disable(): bool
    {
        return $this->update(['status' => self::STATUS_DISABLED]);
    }

    /**
     * 查询启用的用户
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', self::STATUS_ENABLED);
    }

    /**
     * 查询指定性别的用户
     */
    public function scopeOfGender($query, int $gender)
    {
        return $query->where('gender', $gender);
    }

    /**
     * 查询最近登录的用户
     */
    public function scopeRecentLogin($query, int $days = 30)
    {
        return $query->where('last_login_at', '>=', now()->subDays($days));
    }
}
