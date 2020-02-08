# Settings plugin
Combines settings from different plugins

To add settings, create file`{plugin_path}/settings/fields.yml` with content:

    {tab_name}:
      permissions: {permission_1|permission_2} // Permissions for the whole group
      order: {order}
      name:
        label: {label}
        span: {span}
        type: {type}
        cast: {integer}
        default: {default}
        permissions: {permission_1|permission_2}  // Permissions for the field
        order: {order}
        ...
      ...
    ...