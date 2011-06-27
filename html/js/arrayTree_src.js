

/**
 * Class ArrayTree
 *
 * Provide methods to handle Ajax requests on ArrayTree.
 * Based on AjaxRequest from Leo Feyer
 * 
 * @package    wd
 */
var ArrayTree =
{
		/**
		 * Toggle a group of a multi-checkbox field
		 * @param object
		 * @param string
		 */
		toggleArrayTreeGroup: function(el, id)
		{
			el.blur();

			var item = $(id);
			var image = $(el).getFirst();

			if (item)
			{
				if (item.getStyle('display') != 'block')
				{
					item.setStyle('display', 'block');
					image.src = image.src.replace('folPlus.gif', 'folMinus.gif');
					new Request({url: window.location.href, data: 'isAjax=1&action=toggleArrayTreeGroup&id=' + id + '&state=1'}).send();
				}
				else
				{
					item.setStyle('display', 'none');
					image.src = image.src.replace('folMinus.gif', 'folPlus.gif');
					new Request({url: window.location.href, data: 'isAjax=1&action=toggleArrayTreeGroup&id=' + id + '&state=0'}).send();
				}

				return true;
			}

			return false;
		}
}