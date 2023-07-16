jQuery(document).ready(function ($) 
{
    if($('#start_datetime').length)
    {
        const dpInstance = new mds.MdsPersianDateTimePicker(document.getElementById('start_datetime'), {
            targetTextSelector: '#start_datetime',
            textFormat: 'yyyy-MM-dd',
            placement: 'bottom',
        });
    }
    if($('#end_datetime').length)
    {
        const dpInstance2 = new mds.MdsPersianDateTimePicker(document.getElementById('end_datetime'), {
            targetTextSelector: '#end_datetime',
            textFormat: 'yyyy-MM-dd',
            placement: 'bottom',
        });
    }
    if($('#date_of_birth').length)
    {
        const dpInstance3 = new mds.MdsPersianDateTimePicker(document.getElementById('date_of_birth'), {
            targetTextSelector: '#date_of_birth',
            textFormat: 'yyyy-MM-dd',
            placement: 'bottom',
        });
    }
});