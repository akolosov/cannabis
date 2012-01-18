FCKConfig.Debug = false;

// FCKConfig.Plugins.Add('autogrow', null);
FCKConfig.Plugins.Add('tablecommands', null);
FCKConfig.Plugins.Add('dragresizetable', null);
FCKConfig.Plugins.Add('documentList', 'ru,en');

FCKConfig.TabSpaces = 8;

FCKConfig. ToolbarSets['Custom'] = [
    ['Save', '-', 'Undo', 'Redo', '-', 'Find', 'Replace', '-', 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteWord'],
    ['Bold', 'Italic', 'Underline', 'StrikeThrough', '-', 'Subscript', 'Superscript', '-', 'JustifyLeft','JustifyCenter','JustifyRight','JustifyFull', '-', 'OrderedList', 'UnorderedList'],
    ['Link', 'Unlink', '-', 'DocumentList', '-', 'Image', 'SpecialChar', 'PageBreak'],
    ['FontName', 'FontSize'],
    ['TextColor', 'BGColor'],
    ['Table', '-', 'TableInsertRowAfter', 'TableDeleteRows', 'TableInsertColumnAfter', 'TableDeleteColumns',
     'TableInsertCellAfter', 'TableDeleteCells', 'TableMergeCells'],
    ['FitWindow', '-', 'About']
];
