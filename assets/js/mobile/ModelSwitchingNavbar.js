/**
 * This is a controller for model-switching navbar.
 * We have three different models of sunglasses.
 * When user clicks one of navbar model icons, 
 * This script shows the clicked model, and hides all other models.
 */ 
$(document).ready(function() {
    var modelNames = ['Ken Block Helm', 'Flynn', 'Touring'];

    //console.debug('hash: ', modelFromHash);

    var selectedModel;

    var modelFromHash = decodeURI(window.location.hash.substr(1));
    if (modelFromHash) {
        //console.debug('setting model from hash');
        selectedModel = modelFromHash;
    } else {
        selectedModel = modelNames[0];
    }

    var sunglasses = $('.sunglasses-item'); 
    var modelSwitchingButtons = $('.model-switching-button');

    initButtonListeners();
    initSwipeListeners();

    showModel(selectedModel);

    function showModel(model) {
        setSelectedClassForButton();
        window.location.hash = selectedModel;

        sunglasses.each(function(index, sunglassesItem) {
            var $sunglassesItem = $(sunglassesItem);
            if ($sunglassesItem.attr('data-model') === model) {
                $sunglassesItem.show();
            } else {
                $sunglassesItem.hide();
            }
        });
    }

    function initButtonListeners() {
        modelSwitchingButtons.on('click', function(event) {
            event.preventDefault();
            selectedModel = $(this).attr('data-model');
            showModel(selectedModel);
            updateJqueryLazyLoad();
            return false;
        });
    }

    function initSwipeListeners() {
        $(document.body).on('swipeleft', function(event) {
            nextModel();
        });

        $(document.body).on('swiperight', function(event) {
            previousModel();
        });
    }

    function nextModel() {
        var index = modelNames.indexOf(selectedModel);
        index = (index + 1) % 3;
        selectedModel = modelNames[index];
        showModel(selectedModel);
        updateJqueryLazyLoad();
    }

    function previousModel() {
        var index = modelNames.indexOf(selectedModel);
        index--;
        if (index < 0) index = 2;
        selectedModel = modelNames[index];
        showModel(selectedModel);
        updateJqueryLazyLoad();
    }

    function setSelectedClassForButton() {
        modelSwitchingButtons.each(function(index, button) {
            $button = $(button);
            var model = $button.attr('data-model');
            if (model !== selectedModel) {
                $button.removeClass('selected');
            } else {
                $button.addClass('selected');
            }
        });
    }

    function updateJqueryLazyLoad() {
        // trigerring scroll event on window,
        // makes jquery.lazyload load images
        // that are in viewport.
        $(window).scroll();
    }

});
