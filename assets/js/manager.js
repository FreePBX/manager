function linkFormatter(value, row, idx)
{
    var html = sprintf('<a href="#" data-toggle="modal" data-target="#managerForm" data-id="%s"><i class="fa fa-pencil"></i></a>', row['manager_id']);
    html += '&nbsp;';
    html += sprintf('<a href="#" data-id="%s" data-name="%s" id="del" data-idx="%s"><i class="fa fa-trash"></i></a>', row['manager_id'], row['name'], idx);
    return html;
}

$(document).on('click', '[id="del"]', function (e)
{
    e.preventDefault();

	var id   = $(this).data('id');
    var name = $(this).data('name');

	if (id === "" || id === undefined || id === null)
	{
		fpbxToast(_("ID not detected!"), '', 'error');
		return;
	}

	fpbxConfirm(
		sprintf(_("Are you sure you want to delete (%s)?"), name),
		_("Yes"), _("No"),
		function () {
			var post_data = {
				module: 'manager',
				command: 'delete',
				id: id,
			};
			$.post(window.FreePBX.ajaxurl, post_data)
			.done(function (data)
			{
				if (data.status == true)
				{
                    getTableGrid().bootstrapTable('refresh', { silent: true });
				}
				fpbxToast(data.message, '', data.status == true ? 'success' : 'error');
                if (data.needreload)
                {
                    showButtonReloadFreePBX();
                }
			})
			.fail(function(jqXHR, textStatus, errorThrown)
			{
				fpbxToast(textStatus + ' - ' + errorThrown, '', 'error');
			});
		}
	);
});

function showButtonReloadFreePBX()
{
	$("#button_reload").show();
}

function getTableGrid()
{
    return $('#managersgrid');
}

$('#managerForm').on('hidden.bs.modal', function ()
{
    $("#idManager").val("");
    $("#nameManager").val("");
    $("#secretManager").val("");    
});

$('#managerForm').on('shown.bs.modal', function ()
{
    // Force focus to generate password strength level bar.
    let elementNow = $(document.activeElement);
    $("#secretManager").focus();
    $(elementNow).focus();
});

$('#managerForm').on('show.bs.modal', function (e)
{
	var id = $(e.relatedTarget).data('id');
	var showModal = true;
	
	$('.element-container').removeClass('has-error');
	$(".input-warn").remove();

	var name        = "";
    var dataGet     = null;
    var permissions = null;

    var title 	 = "";
	var btn_send = "";

	var name_readonly = false;

	if (id == null || id == undefined || id == "")
	{
        id       = "-1";
		title 	 = _("New Manager");
		btn_send = _("Create New");

		name_readonly = false;
	}
	else
	{
		title 	 = sprintf(_("Edit Manager (%s)"), id);
		btn_send = _("Save Changes");

		name_readonly = true;
	}

    $.ajax({
        type: "POST",
        url: window.FreePBX.ajaxurl,
        data: {
            module	: 'manager',
            command	: 'get',
            id		: id,
        },
        async: false,
        success: function(response)
        {
            if (response.status)
            {
                name        = response.data.name;
                dataGet     = response.data;
                permissions = response.permissions;
            }
            else
            {
                fpbxToast(response.message, '', 'error');
                showModal = false;
            }
        },
        error: function(xhr, status, error)
        {
            fpbxToast(sprintf(_('Error: %s'), error), '', 'error');
            showModal = false;
        }
    });

	if (showModal)
	{
		$this = this;

        // Active tab main
        var tabMain = document.querySelector('a[href="#managerset"]');
        $(tabMain).tab('show');

        // Config Buttons
		$("#submitForm").text(btn_send);
		$("#submitForm").prop("disabled", false);

        // Config Title
		$(this).find('.modal-title').text(title);
	
        // Set Values
		$("#idManager").val(id);

		$('#nameManager').prop('readonly', name_readonly);
		$("#nameManager").val(name);

        $.each(dataGet, function(key, val)
        {
            let idInput = sprintf("#%sManager", key);
            switch(key)
            {
                case 'read':
                case 'write':
                    break;

                default:
                    $(idInput).val(val);
                    break;
            }
        });

        let iAllCount   = 0;
        let iReadCount  = 0;
        let iWriteCount = 0;
        $.each(permissions, function(key, valor)
        {
            iAllCount++;
            let rCheck = false;
            let wCheck = false;
            if ($.inArray(key, dataGet.read) !== -1)
            {
                iReadCount++;
                rCheck = true;
            }
            if ($.inArray(key, dataGet.write) !== -1)
            {
                iWriteCount++;
                wCheck = true;
            }
            $("#r" + key+ "yes").prop('checked', rCheck);
            $("#r" + key+ "no").prop('checked', !rCheck);
            $("#w" + key+ "yes").prop('checked', wCheck);
            $("#w" + key+ "no").prop('checked', !wCheck);
        });

        $("#rallno").prop('checked', iReadCount == 0);
        $("#rallyes").prop('checked', iReadCount == iAllCount);
        $("#wallno").prop('checked', iWriteCount == 0);
        $("#wallyes").prop('checked', iWriteCount == iAllCount);
	}

	if (!showModal)
	{
        // Abort window opening
		e.preventDefault();
	}
});

$('#submitForm').on('click', function () {
	$this = this;

    var theForm = document.editManager;
    theForm.nameManager.focus();

 	var id = theForm.idManager.value;
    if (id === '' || id === null || id === undefined || id == "-1")
	{
		var typeUpdate = "new";
	}
	else
	{
		var typeUpdate = "edit";
	}

    var errName         = _('The manager name cannot be empty or may not have any space in it.');
    var errInvalidName  = _('The manager name will not accept any special characters except _ and -.');
    var errSecret       = _('The manager secret cannot be empty.');
    var errSecretFormat = _('Manager password must match regex rules like this: [a-zA-Z0-9*?+-.,!_]');
    var errDeny         = _('The manager deny is not well formatted.');
    var errPermit       = _('The manager permit is not well formatted.');

	defaultEmptyOK = false;
	var regex = /^([a-zA-Z0-9 _-]+)$/;
    if(! theForm.nameManager.value.match(regex))
    {
        return warnInvalid(theForm.nameManager, errInvalidName);
	}
	else if ((theForm.nameManager.value.search(/\s/) >= 0) || (theForm.nameManager.value.length == 0))
    {
        return warnInvalid(theForm.nameManager, errName);
    }

	if (theForm.secretManager.value.length == 0)
    {
        return warnInvalid(theForm.secretManager, errSecret);
    }
    var regex = new RegExp("^[a-zA-Z0-9*?+-.,!_]*$");
    if (!regex.test(theForm.secretManager.value))
    {
        return warnInvalid(theForm.secretManager, errSecretFormat);
    } 

	// Only IP/MASK format are checked
	if (theForm.denyManager.value.search(/\b(?:\d{1,3}\.){3}\d{1,3}\b\/\b(?:\d{1,3}\.){3}\d{1,3}\b(&\b(?:\d{1,3}\.){3}\d{1,3}\b\/\b(?:\d{1,3}\.){3}\d{1,3}\b)*$/)) 
    {
        return warnInvalid(theForm.denyManager, errDeny);
    }	
	if (theForm.permitManager.value.search(/\b(?:\d{1,3}\.){3}\d{1,3}\b\/\b(?:\d{1,3}\.){3}\d{1,3}\b(&\b(?:\d{1,3}\.){3}\d{1,3}\b\/\b(?:\d{1,3}\.){3}\d{1,3}\b)*$/))
    {
        return warnInvalid(theForm.permitManager, errPermit);
    }

 	$(this).prop("disabled", true);
	$($this).text( typeUpdate == "edit" ? _("Updating..."): _("Adding..."));

    var formDataArray = $(theForm).serializeArray();
    var formDataObject = {};
    formDataArray.forEach(function(input)
    {
        formDataObject[input.name] = input.value;
    });
    
	var post_data = {
		module: 'manager',
		command: 'update',
        type: typeUpdate,
        id: id,
        formdata: formDataObject,
	};

	$.post(window.FreePBX.ajaxurl, post_data)
  	.done(function(data)
	{
 		if (data.status == true)
		{
            getTableGrid().bootstrapTable('refresh', { silent: true });
 			$("#managerForm").modal('hide');
 		}
        fpbxToast(data.message, '', data.status == true ? 'success' : 'error');
        if (data.needreload)
        {
            showButtonReloadFreePBX();
        }
  	})
  	.fail(function(jqXHR, textStatus, errorThrown)
	{
		fpbxToast(textStatus + ' - ' + errorThrown, '', 'error');
  	})
	.always(function()
	{
		$($this).text( typeUpdate == "edit" ? _("Save Changes"): _("Create New"));
		$($this).prop("disabled", false);
	});
});

$(document).ready(function(){
    $("input[name='rall']").change(function()
    {
        if($(this).val() == 1)
        {
            $("input[name^='r'][type=radio]").each(function()
            {
                var name = $(this).prop('name');
                if(name == 'reset')
                {
                    return;
                }
                if(name == 'rall')
                {
                    return;
                }
                $('#'+name+'yes').prop('checked',true);
                $('#'+name+'no').prop('checked',false);
            })
        }
        else
        {
            $("input[name^='r'][type=radio").each(function()
            {
                var name = $(this).prop('name');
                if(name == 'reset')
                {
                    return;
                }
                if(name == 'rall')
                {
                    return;
                }
                $('#'+name+'no').prop('checked',true);
                $('#'+name+'yes').prop('checked',false);
            })
        }
    });

    $("input[name='wall']").change(function()
    {
        if($(this).val() == 1)
        {
            $("input[name^='w']").each(function()
            {
                var name = $(this).prop('name');
                if(name == 'reset')
                {
                    return;
                }
                if(name == 'wall')
                {
                    return;
                }
                $('#'+name+'yes').prop('checked',true);
                $('#'+name+'no').prop('checked',false);
            })
        }
        else
        {
            $("input[name^='w']").each(function()
            {
                var name = $(this).prop('name');
                if(name == 'reset')
                {
                    return;
                }
                if(name == 'wall')
                {
                    return;
                }
                $('#'+name+'no').prop('checked',true);
                $('#'+name+'yes').prop('checked',false);
            })
        }
    });

    $("input:radio[id^='r'][id*='yes'], input:radio[id^='r'][id*='no']").not('#rallyes, #rallno').change(function ()
    {
        let iAll   = $("input:radio[id^='r'][id$='yes'][id!='rallyes']").length;
        let iCheck = $("input:radio[id^='r'][id$='yes'][id!='rallyes']").filter(':checked').length;
        $("#rallyes").prop('checked', iAll == iCheck);
        $("#rallno").prop('checked', iCheck == 0);
    });

    $("input:radio[id^='w'][id*='yes'], input:radio[id^='w'][id*='no']").not('#wallyes, #wallno').change(function ()
    {
        let iAll   = $("input:radio[id^='w'][id$='yes'][id!='wallyes']").length;
        let iCheck = $("input:radio[id^='w'][id$='yes'][id!='wallyes']").filter(':checked').length;
        $("#wallyes").prop('checked', iAll == iCheck);
        $("#wallno").prop('checked', iCheck == 0);
    });

});