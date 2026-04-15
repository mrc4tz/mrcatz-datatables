<?php

namespace MrCatz\DataTable;

/**
 * Central registry of all Livewire event names used by MrCatz DataTable.
 */
final class MrCatzEvent
{
    // Component ↔ View events
    public const ADD_DATA = 'add-data';
    public const EDIT_DATA = 'edit-data';
    public const DELETE_DATA = 'delete-data';
    public const REFRESH_DATA = 'refresh-data';
    public const SHOW_NOTIF = 'show-notif';
    public const NOTICE = 'notice';
    public const RESET_SELECT = 'reset-select';
    public const OPEN_EXPORT_MODAL = 'open-export-modal';
    public const SEARCH_TYPING = 'search-typing';

    // Page ↔ Table lifecycle events
    public const PREPARE_ADD = 'prepareAddData';
    public const PREPARE_EDIT = 'prepareEditData';
    public const PREPARE_DELETE = 'prepareDeleteData';
    public const BULK_DELETE = 'bulkDeleteData';
    public const BULK_ACTION = 'bulkActionData';
    public const BULK_ACTION_OPEN = 'bulkActionOpen';
    public const BULK_ACTION_DONE = 'bulkActionDone';
    public const REFRESH_TABLE = 'refreshDataTable';
    public const INLINE_UPDATE = 'inlineUpdateData';
    public const ROW_CLICK = 'rowClickData';
}
