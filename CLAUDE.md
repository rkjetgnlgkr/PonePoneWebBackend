# CLAUDE.md — PonePoneWebBackend

公開展示 API 後端。無需登入，提供個人主頁資料給前台展示用。

## 技術棧

- **PHP 8.1**（必須用完整路徑 `/opt/homebrew/opt/php@8.1/bin/php`，PHP 7.x 無法連 MySQL 8.4）
- **Laravel 9**（從 Laravel 7 + PHP 7.2 升級而來）
- **Eloquent ORM**
- **MySQL 8.4**（使用 `caching_sha2_password`，需 PHP 8.1+）

## 啟動指令

```bash
# 啟動 API server（port 8000）
/opt/homebrew/opt/php@8.1/bin/php artisan serve --port=8000

# 清除 rate limit 快取（優先用直接刪檔，artisan 有時無效）
rm -rf storage/framework/cache/data/*
/opt/homebrew/opt/php@8.1/bin/php artisan cache:clear
```

## 專案結構

```
app/
  Http/
    Controllers/Api/
      UserProfileController.php   # 唯一 API controller
    Kernel.php                    # throttle:300,1 設定在此
    Middleware/
      TrustProxies.php            # 繼承 Illuminate\Http\Middleware\TrustProxies（Laravel 9 版）
  Models/
    User.php                      # 含 portfolios / skills / workExperiences / socialLinks 關聯
    Portfolio.php                 # hasMany PortfolioImage
    PortfolioImage.php            # timestamps = false
    Skill.php
    WorkExperience.php            # is_current cast boolean；日期 cast date:Y-m-d
    SocialLink.php                # timestamps = false
    LayoutConfig.php              # 主題設定，one-to-one with User
config/
  cors.php                        # allowed_origins: ['*']
routes/
  api.php                         # 僅一條路由：GET /public/{username}
```

## API

**Base path：** `/api`（`routes/api.php` 透過 `RouteServiceProvider` 自動加 prefix）

| Method | Path | 說明 |
|--------|------|------|
| GET | `/api/public/{username}` | 回傳完整個人主頁資料 |

`{username}` 限制正則：`[a-zA-Z0-9_\-]+`

### 回應結構

```json
{
  "code": 200,
  "message": "ok",
  "data": {
    "user": { "id", "username", "nickname", "email", "phone", "title", "bio", "avatar", "location" },
    "portfolios": [{ "id", "name", "description", "url", "images": [{"id", "image_path"}] }],
    "skills": [{ "id", "name", "level", "category", "sort_order" }],
    "experiences": [{ "id", "company", "position", "start_date", "end_date", "is_current", "description", "sort_order" }],
    "social_links": [{ "id", "platform", "url", "sort_order" }],
    "layout": { "theme_style": "dark_star" }
  }
}
```

404 時：`{ "code": 404, "message": "找不到該使用者", "data": null }`

### layout.theme_style 合法值

| 值 | 主題 |
|----|------|
| `dark_star` | 深色星空（預設，無 layout_config 記錄時使用） |
| `nature` | 大自然唯美風（清新森林系） |
| `terminal` | Terminal 駭客風（程式碼粒子背景） |

`layout_config` 表的 `theme_style` 欄位有 CHECK constraint 限制以上三個值。

## Models 重點

| Model | Table | 特殊設定 |
|-------|-------|---------|
| `User` | `users` | `hidden: ['password', 'remember_token', 'google_id']` |
| `Portfolio` | `portfolios` | `hasMany(PortfolioImage)` |
| `PortfolioImage` | `portfolio_images` | `$timestamps = false` |
| `SocialLink` | `social_links` | `$timestamps = false` |
| `WorkExperience` | `work_experiences` | `is_current` cast boolean；日期 cast `date:Y-m-d` |
| `LayoutConfig` | `layout_config` | one-to-one with User（`UNIQUE(user_id)`） |

## 關聯查詢順序

- `skills` → `orderBy('sort_order')`
- `workExperiences` → `orderBy('sort_order')`
- `socialLinks` → `orderBy('sort_order')`
- `portfolios` → `orderByDesc('created_at')`，eager load `images`

## Middleware

| Middleware | 設定 |
|-----------|------|
| CORS | `config/cors.php`：paths `api/*`，allowed_origins `['*']`，內建 `HandleCors`（非 Fruitcake） |
| Rate Limit | `app/Http/Kernel.php` api group：`throttle:300,1`（每分鐘 300 次） |

## 環境變數（.env）

| 變數 | 本地預設值 | 說明 |
|------|-----------|------|
| `APP_ENV` | `local` | |
| `APP_URL` | `http://localhost:8000` | |
| `FRONTEND_URL` | `http://localhost:3001` | CORS 參考用 |
| `DB_HOST` | `127.0.0.1` | |
| `DB_PORT` | `3306` | |
| `DB_DATABASE` | `pone_website` | |
| `DB_USERNAME` | `root` | |
| `DB_PASSWORD` | `qwerty789` | |
| `CACHE_DRIVER` | `file` | rate limit 快取存於 `storage/framework/cache/data/` |

## Docker

`Dockerfile`（PHP 8.1 + Apache）與 `.dockerignore` 已就位，可直接容器化部署：

```bash
docker build -t ponepone-backend .
docker run -p 8000:80 \
  -e DB_HOST=host.docker.internal \
  -e DB_PASSWORD=qwerty789 \
  ponepone-backend
```

- Base image：`php:8.1-apache`
- Composer install 於 build 時執行（`--no-dev --optimize-autoloader`）
- Apache DocumentRoot 指向 `/public`，`mod_rewrite` 已啟用
- `.env` 不打包進 image，敏感值透過環境變數注入

## Known Gotchas

- **Rate Limit 429**：開發測試容易超過 throttle。清除方式：`rm -rf storage/framework/cache/data/*`（比 `artisan cache:clear` 更可靠）
- **PHP 版本**：系統 `php` 指令可能仍指向舊版（7.x），務必用完整路徑 `/opt/homebrew/opt/php@8.1/bin/php`
- **MySQL 8.4 認證**：`caching_sha2_password` 為預設，PHP 8.1+ 原生支援，PHP 7.x 不支援（`[2054]` 錯誤）
- **Laravel 9 升級注意**：`TrustProxies` 繼承 `Illuminate\Http\Middleware\TrustProxies`（不再用 `Fideloper\Proxy`）；`HandleCors` 改用內建版本；`CheckForMaintenanceMode` → `PreventRequestsDuringMaintenance`
