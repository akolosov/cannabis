var DocumentListCommand = function() {
};

DocumentListCommand.prototype.Execute = function() {
}

DocumentListCommand.GetState = function() {
	return FCK_TRISTATE_OFF;
}

DocumentListCommand.Execute = function () {
}

FCKCommands.RegisterCommand('DocumentList', new FCKDialogCommand('DocumentList', FCKLang.DocumentListDlgTitle, FCKPlugins.Items['documentList'].Path + 'fck_documentlist.php', 440, 370));

var oDocumentLists = new FCKToolbarButton('DocumentList', FCKLang.DocumentListBtn);
oDocumentLists.IconPath = FCKPlugins.Items['documentList'].Path + 'documentlist.png';
FCKToolbarItems.RegisterItem( 'DocumentList', oDocumentLists);
