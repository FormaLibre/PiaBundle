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
        success: function (datas) {
            openFormModal(
                "Ajouter une tâche",
                datas
            );
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
$('.closeSuivi').on('click', function (event) {
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
