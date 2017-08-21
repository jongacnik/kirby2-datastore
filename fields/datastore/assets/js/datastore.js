(function($) {

  function tinyBars (str, data) {
    var regex = /{{\s*([\w\.]+)\s*}}/gi
    return str.replace(regex, function (match, val) {
      return data[val.trim()] || ''
    })
  }

  var Datastore = function(el) {

    var element = $(el);
    var table = element.find('table')
    var columns = table.data('datastore-columns');
    var entriesapi = table.data('datastore-entries');
    var addapi = table.data('datastore-add');
    var updateapi = table.data('datastore-update');
    var deleteapi = table.data('datastore-delete');
    var rows = table.data('datastore-rows');
    var order = table.data('datastore-order');

    var headers = Object.keys(columns).map(function (key) {
      if ($.isPlainObject(columns[key])) {
        return $('<th>' + columns[key].label + '</th>')
      } else {
        return $('<th>' + columns[key] + '</th>')
      }
    })

    table.append(tableHead(headers, $('<thead></thead>')))
    table.append(tableHead(headers, $('<tfoot></tfoot>')))

    var colCount = Object.keys(columns).length
    
    var defs =  [
      { orderable: false, targets: [colCount, colCount + 1] }
    ]

    var table = table.DataTable({
      columnDefs: defs,
      pageLength: rows,
      order: [[ 0, order ]],
      ajax: {
        url: entriesapi,
        dataSrc: function (json) {
          var formatted = Object.keys(json).map(function (k) {
            var i = json[k]
            var result = []
            
            Object.keys(columns).forEach(function (key) {
              var item = columns[key]
              if ($.isPlainObject(item)) {
                if (item.value) {
                  result.push(tinyBars(item.value, i))
                } else if (item.date) {
                  result.push(new Date(i[key] * 1000).format(item.date))
                } else {
                  result.push(i[key])
                }
              } else {
                result.push(i[key])
              }
            })

            return result.concat([editButton(i._rid), deleteButton(i._rid)])
          })

          return formatted
        }
      }
    });

    // click row to edit
    table.on('click', 'tbody tr', function (e) {
      var $target = $(e.target)
      if (!$target.is('i') && !$target.is('a')) {
        var $edit = $(e.currentTarget).find('.structure-edit-button')
        if ($edit.length) $edit.get(0).click()
      }
    })

    function editButton (id) {
      return ' \
        <a data-modal class="btn structure-edit-button" href="' + updateapi.replace('/update', '/' + id + '/update') + '"> \
          <i class="icon fa fa-pencil"></i> \
        </a> \
      '
    }

    function deleteButton (id) {
      return ' \
        <a data-modal class="btn structure-delete-button" href="' + deleteapi.replace('/delete', '/' + id + '/delete') + '"> \
          <i class="icon fa fa-trash"></i> \
        </a> \
      '
    }

    function tableHead (headers, $element) {
      var $row = $('<tr></tr>')
      headers.forEach(function ($header) {
        $row.append($header.clone())
      })

      // edit/del cols
      $row.append($('<th width="18"></th>'))
      $row.append($('<th width="18"></th>'))

      return $element.append($row);
    }

  };

  $.fn.datastore = function() {

    return this.each(function() {

      if($(this).data('datastore')) {
        return $(this);
      } else {
        var datastore = new Datastore(this);
        $(this).data('datastore', datastore);
        return $(this);
      }

    });

  };

})(jQuery);