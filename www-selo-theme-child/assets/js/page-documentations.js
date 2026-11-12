(function($){
  function openAccordionAncestors($el){
    $el.parents('.accordion-collapse').addClass('show'); // abre todos os pais
    $el.parents('.accordion-item')
       .find('> .accordion-header > .accordion-button')
       .removeClass('collapsed');
  }

  function markActiveFromWP(){
    // 1) aria-current
    var $link = $('#sidebarMenu a[aria-current="page"]').first();
    if ($link.length){
      $('#sidebarMenu a').removeClass('active');
      $link.addClass('active');
      openAccordionAncestors($link);
      return true;
    }
    // 2) classes do WP nos <li>
    var $li = $('#sidebarMenu li.current-menu-item, \
                 #sidebarMenu li.current_page_item, \
                 #sidebarMenu li.current-menu-ancestor, \
                 #sidebarMenu li.current_page_ancestor').first();
    if ($li.length){
      $('#sidebarMenu a').removeClass('active');
      var $a = $li.children('a').first();
      if ($a.length){ $a.addClass('active'); openAccordionAncestors($a); }
      else { openAccordionAncestors($li); }
      return true;
    }
    return false;
  }

  function markActiveFromURL(){
    var path = (window.location.pathname || '').replace(/\/+$/,'');
    var slug = path.split('/').filter(Boolean).pop();
    if (!slug) return false;

    var $a = $('#sidebarMenu a[href]').filter(function(){
      var href = ($(this).attr('href') || '').replace(/\/+$/,'');
      if (!href || href[0] === '#') return false;
      var hslug = href.split('/').filter(Boolean).pop();
      return hslug === slug;
    }).first();

    if ($a.length){
      $('#sidebarMenu a').removeClass('active');
      $a.addClass('active');
      openAccordionAncestors($a);
      return true;
    }
    return false;
  }

  function markActive(){
    if (markActiveFromWP()) return;
    markActiveFromURL();
  }

  // Marca ao carregar
  $(markActive);

  // Feedback imediato ao clicar (sem impedir navegação)
  $('#sidebarMenu').on('click', 'a[href]:not([href^="#"])', function(){
    $('#sidebarMenu a').removeClass('active');
    $(this).addClass('active');
    openAccordionAncestors($(this));
  });
})(jQuery);