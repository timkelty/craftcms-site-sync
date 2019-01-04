# Site-Sync

Synchronize content between sites in a multi-site [Craft CMS](https://craftcms.com/) installation.


---

## Why?

In a [multi-site](https://docs.craftcms.com/v3/sites.html) configuration, translatable field content is propagated to other sites _only on initial save_. If you want to edit translatable content and propagate those changes to your other sites, you have to recreate the changes on each site. 🤢

**Site-Sync** allows you to propagate and sychronize changes on a per-field basis, whenever you save an element. In addition to any field content (including Matrix, et al.), it can also sync `title` and `slug` content, as those are *always* translatable in Craft.

Unlike the [Craft 2 version](https://github.com/timkelty/craft-site-sync/tree/craft-2) and [other similar plugins](https://github.com/Goldinteractive/craft3-sitecopy), this plugin is implemented as a field for a few key reasons:

- It works when saving an element in the HUD.
- Multiple fields can be created with different default settings.

## Usage

Each field layout where you would like to perform syncing must contain a **Site-Sync Settings** field.

![Site-Sync Settings Field](https://raw.githubusercontent.com/timkelty/craft-site-sync/master/resources/screenshot-field.png)

In the field settings you can configure the default state of the field. Note, that unlike most fields, changes to this field do not persist between element saves. They will always revert to the state configured in the field settings.

![Site-Sync Settings Field](https://raw.githubusercontent.com/timkelty/craft-site-sync/master/resources/screenshot-field-settings.png)

When you save an entry with a **Site-Sync Settings** field:

- If the **Enabled** toggle is on when you save an element, content that matched before saving will be propagated to the other sites.
- Only content from the selected **Sources** will be synchronized.
- Content will only be propagated to sites where the user has permission to save elements.

### Nesting (Matrix, Neo, SuperTable, et al.)

When _any_ element (e.g. `MatrixBlock`, `Entry`) is saved, the plugin will traverse up its hierachy (via [`ElementInterface::getParent`](https://docs.craftcms.com/api/v3/craft-base-elementinterface.html#method-getparent)) until it finds a **Site-Sync Settings** field.

For example, this means you can have a single **Site-Sync Settings** field on your `Entry` layout, but a nested `MatrixBlock` will still find it and use those settings for syncing.

This also means (if you want to get crazy), that you could include a **Site-Sync Settings** field on a `MatrixBlock` to limit the scope of the syncing to that block only. Or, you could include one on the `Entry` layout and override it with another on a **MatrixBlock** layout. While this approach is supported, it can get exponentially confusing for the user and likely isn't practical for most uses.

## Requirements

- Craft ^3.0.28
- PHP ^7.0

## Installation

```shell
$ composer require timkelty/craft-site-sync:^1.0.0
```

## Roadmap

- [ ] Plugin store
- [ ] Validate field layouts to only allow 1 of this field type
- [ ] Value/label usability improvements (`toggleLabelId`)
- [ ] Fix compatibility with CP Field Links
- [ ] Change "Title" name if there is a custom.
- [ ] Implement getElementValidationRules to add errors to the element
- [ ] Ensure elements are only saved when they have pending changes (performance)
