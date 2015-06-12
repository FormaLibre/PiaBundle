function openFormModal(title, content)
{
    $('#form-modal-title').html(title);
    $('#form-modal-body').html(content);
    $('#form-modal-box').modal('show');
}

function closeFormModal()
{
    $('#form-modal-box').modal('hide');
    $('#form-modal-title').empty();
    $('#form-modal-body').empty();
}

// Click on widget create button
$('.addTache').on('click', function (event) {
    userId = $(event.target).attr('data-user-id')
    $.ajax({
        url: Routing.generate(
            'laurentPiaTacheAdd', {'user':userId}
        ),
        type: 'GET',
        success: function(datas, textStatus, jqXHR) {
            switch (jqXHR.status) {
                case 202:
                    closeFormModal();
                    window.location.reload();
                    break;
                default:
                    openFormModal(
                        "Ajouter une tâche",
                        datas
                    );
                    break;
            }
        }
    });
});

// Click on widget create button
$('.viewSuivi').on('click', function (event) {
    tacheId = $(event.target).attr('data-tache-id')
    $.ajax({
        url: Routing.generate(
            'laurentPiaSuivi', {'tache':tacheId}
        ),
        type: 'GET',
        success: function (datas) {
            openFormModal(
                "Suivis",
                datas
            );
        }
    });
});

// Click on widget create button
$('.closeTache').on('click', function (event) {
    tacheId = $(event.target).attr('data-tache-id')
    $.ajax({
        url: Routing.generate(
            'laurentPiaTacheClose', {'tache':tacheId}
        ),
        type: 'GET',
        success: function(datas, textStatus, jqXHR) {
            switch (jqXHR.status) {
                case 202:
                    window.location.reload();
                    break;
                default:
                    window.location.reload();
                    break;
            }
        }
    });
});

// Click on widget create button
$('.deleteTache').on('click', function (event) {
    tacheId = $(event.target).attr('data-tache-id')
    $.ajax({
        url: Routing.generate(
            'laurentPiaTacheDelete', {'tache':tacheId}
        ),
        type: 'GET',
        success: function(datas, textStatus, jqXHR) {
            switch (jqXHR.status) {
                case 202:
                    window.location.reload();
                    break;
                default:
                    window.location.reload();
                    break;
            }
        }
    });
});

// Click on widget create button
$('.editTache').on('click', function (event) {
    tacheId = $(event.target).attr('data-tache-id')
    $.ajax({
        url: Routing.generate(
            'laurentPiaTacheEdit', {'tache':tacheId}
        ),
        type: 'GET',
        success: function(datas, textStatus, jqXHR) {
            switch (jqXHR.status) {
                case 202:
                    window.location.reload();
                    break;
                default:
                    openFormModal(
                        "Tache",
                        datas
                    );
                    break;
            }
        }
    });
});

// Click on widget create button
$('.editPeriodeBtn').on('click', function (event) {
    var periodeId = $(event.currentTarget).data('periode-id');
    $.ajax({
        url: Routing.generate(
            'laurentBulletinPeriodeEdit', {'periode': periodeId}
        ),
        type: 'GET',
        success: function (datas) {
            openFormModal(
                "Modifier une période",
                datas
            );
        }
    });
});

// Click on OK button of the Create Widget instance form modal
$('body').on('click', '#form-tache-ok-btn', function (e) {
    e.stopImmediatePropagation();
    e.preventDefault();

    var form = document.getElementById('Tache-form');
    var action = form.getAttribute('action');
    var formData = new FormData(form);

    $.ajax({
        url: action,
        data: formData,
        type: 'POST',
        processData: false,
        contentType: false,
        success: function(datas, textStatus, jqXHR) {
            window.location.reload();

        }
    });
});

$('#constat-create-btn').on('click', function () {
    var userId = $(this).data('user-id');
        
    window.Claroline.Modal.displayForm(
        Routing.generate(
            'laurentPiaConstatCreateForm',
            {'user': userId}
        ),
        addConstat,
        function() {}
    );
});
    
$('#constat-body-box').on('click', '.edit-constat-btn', function () {
    var constatId = $(this).data('constat-id');
        
    window.Claroline.Modal.displayForm(
        Routing.generate(
            'laurentPiaConstatEditForm',
            {'constat': constatId}
        ),
        editConstat,
        function() {}
    );
});
    
$('#constat-body-box').on('click', '.delete-constat-btn', function () {
    var constatId = $(this).data('constat-id');

    window.Claroline.Modal.confirmRequest(
        Routing.generate(
            'laurentPiaConstatDelete',
            {'constat': constatId}
        ),
        removeConstat,
        constatId,
        'Etes-vous sûr de vouloir supprimer ce constat ?',
        'Suppression du constat'
    );
});

var addConstat = function (datas) {
    var id = datas['id'];
    var content = datas['content'];
    var creationDate = datas['creationDate'];
    
    var element = 
        '<hr id="constat-separator-' + id + '">' +
        '<div id="constat-box-' + id + '">' +
            '<i class="delete-constat-btn fa fa-times close"' +
               'data-constat-id="' + id + '"' +
            '>' +
            '</i>' +
            '<span class="close">&nbsp;</span>' +
            '<i class="edit-constat-btn fa fa-pencil close"' +
               'data-constat-id="' + id + '"' +
            '>' +    
            '</i>' +
            content +
            '<small class="text-muted">' +
                'Créé le ' + creationDate + '.' +
            '</small>' +
        '</div>';

    $('#constat-body-box').append(element);    
};

var editConstat = function (datas) {
    var id = datas['id'];
    var content = datas['content'];
    var creationDate = datas['creationDate'];
    var editionDate = datas['editionDate'];
    $('#constat-box-' + id).empty();
    
    var constatContent = 
        '<i class="delete-constat-btn fa fa-times close"' +
           'data-constat-id="' + id + '"' +
        '>' +
        '</i>' +
        '<span class="close">&nbsp;</span>' +
        '<i class="edit-constat-btn fa fa-pencil close"' +
           'data-constat-id="' + id + '"' +
        '>' +    
        '</i>' +
        content +
        '<small class="text-muted">' +
            'Créé le ' + creationDate + '. Dernière modification: ' + editionDate + '.' +
        '</small>';

    $('#constat-box-' + id).html(constatContent);
};

var removeConstat = function (event, constatId) {
    $('#constat-box-' + constatId).remove();
    $('#constat-separator-' + constatId).remove();
};
