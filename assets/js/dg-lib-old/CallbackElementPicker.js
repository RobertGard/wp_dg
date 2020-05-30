function getDomPath(el) {
    const stack = [];
    while (el.parentNode) {
        let sibCount = 0;
        let sibIndex = 0;
        for (let i = 0; i < el.parentNode.childNodes.length; i++) {
            let sib = el.parentNode.childNodes[i];
            if (sib.nodeName === el.nodeName) {
                if (sib === el) {
                    sibIndex = sibCount;
                }
                sibCount++;
            }
        }
        if (el.hasAttribute('id') && el.id !== '') {
            stack.unshift(el.nodeName.toLowerCase() + '#' + el.id);
        } else if (sibCount > 1) {
            stack.unshift(el.nodeName.toLowerCase() + ':eq(' + sibIndex + ')');
        } else {
            stack.unshift(el.nodeName.toLowerCase());
        }
        el = el.parentNode;
    }
    return stack.join(' > ');
}
new ElementPicker({
    container: document.body,
    selectors: '*',
    background: 'rgba(255, 0, 0, 0.5)',
    borderWidth: 0,
    ignoreElements: [document.body],
    action: {
        trigger: 'click',
        callback: function(target) {
            target.classList.toggle('highlight');
            alert(getDomPath(target));
        }
    }
});