( function() {
    tinymce.PluginManager.add( 'wpac_editor', function( editor, url ) {

        // Add a button that opens a window
        editor.addButton( 'wpac_button_key', {

            icon: true,
            image: url + '/img/allocine.png',
            onclick: function() {
                // Open window
                editor.windowManager.open( {
                    title: 'Entrez un Code AlloCine',
                    body: [
                            { type: 'textbox', name: 'allocine', label: 'Code'},
                            { type: 'listbox', name: 'type', label: 'Type', 'values': [{text: 'Film', value: 'id'}, {text: 'Série', value: 'seid'}, {text: 'Episode', value: 'epid'}] },
                            { type: 'listbox', name: 'display', label: 'Affichage', 'values': [{text: 'Normal', value: 'normal'}, {text: 'Petit', value: 'small'}, {text: 'Très petit', value: 'tiny'}, {text: 'Personnalisé', value: 'custom'}] },
                        ],
                    onsubmit: function( e ) {
                        // Insert content when the window form is submitted
                        editor.insertContent( '[wpac ' + e.data.type + '=' + e.data.allocine + ' display="' + e.data.display +'"]'  );
                    }

                } );
            }

        } );

    } );

} )();

