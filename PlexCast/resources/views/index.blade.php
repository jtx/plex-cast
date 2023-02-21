<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PlexCast - Edit the Cast Members in Plex!</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/style.css" />
</head>
<body>
<div class="container">
    <h1 align="right">PlexCast</h1>

    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#movies">Movies</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#actors">Actors</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="movies" class="tab-pane fade show active">
            <label for="movie-search">Search for a movie: </label>
            <input type="text" id="movie-search" class="form-control">
            <dl id="movie-details"></dl>
        </div>
        <div id="actors" class="tab-pane fade">
            <label for="actor-search">Search for an actor: </label>
            <input type="text" id="actor-search" class="form-control">
            <dl id="actor-details"></dl>
        </div>
    </div>
</div>

<div class="modal" tabindex="-1" role="dialog" id="myModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cast Billing</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul id="sortable-list" class="ui-sortable">>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary">Save changes</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function () {
        // Set up autocomplete for movie search
        $("#movie-search").autocomplete({
            source: function (request, response) {
                if (request.term.length >= 2) {
                    $.ajax({
                        url: "{{ route('api.metadataItem.search') }}",
                        dataType: "json",
                        data: {
                            name: request.term
                        },
                        success: function (data) {
                            if (data.length === 0) {
                                response([{ label: "No results found" }]);
                            } else {
                                response($.map(data, function (item) {
                                    return {
                                        label: item.title,
                                        id: item.id
                                    }
                                }));
                            }
                        }
                    });
                }
            },
            minLength: 2,
            select: function (event, ui) {
                let metadataUrl = "{{ route('api.metadataItem.get', ':slug') }}";
                metadataUrl = metadataUrl.replace(':slug', ui.item.id);
                $.ajax({
                    url: metadataUrl,
                    dataType: "json",
                    success: function (data) {
                        $("#movie-details").empty();
                        $.each(data, function (key, value) {
                            $("#movie-details").append("<dt>" + key + "</dt><dd>" + value + "</dd>");
                        });
                    }
                });
            }
        });

        // Set up autocomplete for actor search
        $("#actor-search").autocomplete({
            source: function (request, response) {
                if (request.term.length >= 2) {
                    $.ajax({
                        url: "{{ route('api.tag.search') }}",
                        dataType: "json",
                        data: {
                            name: request.term
                        },
                        success: function (data) {
                            if (data.length === 0) {
                                response([{ label: "No results found" }]);
                            } else {
                                response($.map(data, function (item) {
                                    return {
                                        label: item.tag,
                                        id: item.id
                                    }
                                }));
                            }
                        }
                    });
                }
            },
            minLength: 2,
            select: function (event, ui) {
                let actorUrl = "{{ route('api.tag.get', ':slug') }}";
                actorUrl = actorUrl.replace(':slug', ui.item.id);

                $.ajax({
                    url: actorUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        // clear previous data
                        $('#actor-details').empty();
                        // populate with new data
                        $('#actor-details').append('<div class="row"><div class="col-12 col-md-3"><span>Actor: </span></div><div class="col-12 col-md-9"><span>' + data['tag'] + '</span></div></div>');

                        if (data['user_thumb_url']) {
                            $('#actor-details').append('<div class="row"><div class="col-12 col-md-3"><span>Image: </span></div><div class="col-12 col-md-9"><span><img src="' + data['user_thumb_url'] + '" class="cast-crew-member-thumb img-fluid rounded-circle"/></span></div></div>');
                        }

                        $('#actor-details').append('<div class="row"><div class="col-12 col-md-3"><span>Movies: </span></div></div>');
                        for (var i = 0; i < data.tagging.length; i++) {
                            let tag = data.tagging[i];
                            if (tag.metadata_items !== null) {
                                let metaUrl = "{{ route('api.metadataItem.get', ':slug') }}";
                                metaUrl = metaUrl.replace(':slug', tag.metadata_items.id);

                                $('#actor-details').append('<div class="row"><div class="col-12 col-md-3"> </div><div class="col-12 col-md-9"><span><a data-id="' + tag.metadata_items.id + '" href="#" class="movielink">' + tag.metadata_items.title + '</a></span></div></div>');
                            }
                        }
                    },
                    error: function () {
                        alert('Error retrieving actor information.');
                    }
                });
            }
        });

        $(document).on('click', '.movielink', (function (event) {
            event.preventDefault();
            let id = $(this).data('id');
            openSortableListModal(id);
        }));

    });

    function openSortableListModal(id) {
        let sortUrl = "{{ route('api.tagging.metadataitem.get', ':slug') }}";
        sortUrl = sortUrl.replace(':slug', id);

        $.ajax({
            url: sortUrl,
            dataType: "json",
            success: function (data) {
                let list = $('<ul>').addClass('sortable-list');
                $.each(data, function (index, value) {
                    let item = $('<li>').attr('id', 'billing_' + value.id).addClass('ui-state-default').text(value.tag);
                    list.append(item);
                });

                let modal = $('<div>').attr('id', 'sortable-list-modal').attr('title', 'Cast Billing Order').append(list);

                modal.dialog({
                    autoOpen: true,
                    modal: true,
                    resizable: true,
                    draggable: true,
                    width: 400,
                    height: 700,
                    buttons: {
                        "Close": function () {
                            $(this).dialog("close");
                        }
                    }
                });

                $('.sortable-list').sortable({
                    axis: 'y',
                    items: 'li',
                    update: function (event, ui) {
                        let sorted = $(this).sortable('serialize');

                        let updateUrl = "{{ route('api.tagging.updateIndex', ':slug') }}";
                        updateUrl = updateUrl.replace(':slug', id);

                        $.ajax({
                            data: sorted,
                            type: 'PUT',
                            url: updateUrl
                        });
                    }
                });
            }
        });
    }

</script>
</body>
</html>
