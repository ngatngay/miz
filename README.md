## tính năng

- Apache 2.4
- MariaDB 11.4
- PHP
    - composer
    - wp cli
- Memcached
- SSL

## yêu cầu

- Debian 11

## install

```
curl -Ls https://raw.githubusercontent.com/ngatngay/miz/refs/heads/main/miz_install.sh | bash
```

## panel
- https://[IP]:9869

Bảo mật cao cấp, sai mật khẩu 5 lần khoá 1 tiếng.

## hệ thống tệp

- /www: rất quan trọng

## hệ thống phân quyền

- website: /www/web (/www/web/a.com, /www/web/b.com,... Các website không thể truy cập file lẫn nhau)
- panel: /www/miz_app

chmod khi lỗi quyền:

```
chown -R www-data:www-data /www/web
```