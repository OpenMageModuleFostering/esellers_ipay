
            var submitted = false;

            function showLoader(url,win,para)
            {
                if ($('checkout-agreements')) {
                    var checkboxes = $$('#checkout-agreements input');
                    for (var i=0, l=checkboxes.length; i<l; i++) {
                        if (!checkboxes[i].checked) {
                            alert('Please agree to all Terms and Conditions before placing the orders.');
                            return false;
                        }
                    }
                }

                submitted = true;
                var step='review';
                Element.show(step+'-please-wait');
                $(step+'-buttons-container').setStyle({opacity:.5});
                $(step+'-buttons-container').descendants().each(function(s) {
                      s.disabled = true;
                });
                
                window.open(url,win,para);
                return true;
            }