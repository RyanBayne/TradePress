jQuery(document).ready(function($) {
    console.log('TradePress Pointers: Script loaded');
    console.log('wp object:', typeof wp);
    console.log('wp.pointer:', typeof wp !== 'undefined' ? typeof wp.pointer : 'wp undefined');
    console.log('tradepressPointers:', typeof tradepressPointers);
    
    if (typeof wp !== 'undefined' && wp.pointer && typeof tradepressPointers !== 'undefined') {
        console.log('TradePress Pointers: All dependencies available');
        console.log('Pointers data:', tradepressPointers);
        
        var pointers = tradepressPointers.pointers || [];
        
        function showPointer(pointer) {
            var $target = $(pointer.target);
            
            if ($target.length === 0) {
                console.log('TradePress Pointers: Target not found for', pointer.id, pointer.target);
                return;
            }
            
            // Check if already dismissed
            $.post(ajaxurl, {
                action: 'tradepress_check_pointer_status',
                pointer: pointer.id,
                nonce: tradepressPointers.nonce
            }).done(function(response) {
                if (response.dismissed) {
                    console.log('TradePress Pointers: Already dismissed', pointer.id);
                    return;
                }
                
                console.log('TradePress Pointers: Showing', pointer.id);
                $target.pointer({
                    content: '<h3>' + pointer.title + '</h3><p>' + pointer.content + '</p>',
                    position: pointer.position || 'top',
                    close: function() {
                        $.post(ajaxurl, {
                            action: 'dismiss-wp-pointer',
                            pointer: pointer.id
                        });
                    }
                }).pointer('open');
            });
        }
        
        function checkDependencyAndShow(pointer) {
            if (!pointer.depends_on) {
                showPointer(pointer);
                return;
            }
            
            $.post(ajaxurl, {
                action: 'tradepress_check_pointer_status',
                pointer: pointer.depends_on,
                nonce: tradepressPointers.nonce
            }).done(function(response) {
                if (response.dismissed) {
                    showPointer(pointer);
                }
            });
        }
        
        // Listen for pointer dismissal to show next in chain
        $(document).on('wp-pointer-close', function(e, pointerId) {
            var nextPointer = pointers.find(function(p) { 
                return p.depends_on === pointerId; 
            });
            if (nextPointer) {
                setTimeout(function() {
                    showPointer(nextPointer);
                }, 500);
            }
        });
        
        // Start showing pointers
        console.log('TradePress Pointers: Starting to show', pointers.length, 'pointers');
        pointers.forEach(function(pointer) {
            console.log('TradePress Pointers: Processing pointer', pointer.id);
            checkDependencyAndShow(pointer);
        });
    } else {
        console.log('TradePress Pointers: Dependencies not met');
        if (typeof wp === 'undefined') console.log('- wp object not available');
        if (typeof wp !== 'undefined' && !wp.pointer) console.log('- wp.pointer not available');
        if (typeof tradepressPointers === 'undefined') console.log('- tradepressPointers not available');
    }
});