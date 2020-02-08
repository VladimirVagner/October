# Settings plugin
Объединяет настройки из разных плагинов

Для добавления настроек, создайте файл `{plugin_path}/settings/fields.yml` с содержимым:

    {tab_name}:
      permissions: {permission_1|permission_2} // Права доступа для всей группы
      name:
        label: {label}
        span: {span}
        type: {type}
        cast: {integer}
        default: {default}
        permissions: {permission_1|permission_2}  // Права доступа для поля
        ...
      ...
    ...