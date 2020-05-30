export function isParent(childSelector = '', parentSelector = '') {
  return (
    childSelector !== parentSelector && childSelector.startsWith(parentSelector)
  );
}

export function isBody(element) {
  if (!element) return false;
  return element === document.body;
}

export function getAllParentsUntilBody(element) {
  if (!element) return;

  const parents = [];
  let currentParent = element.parentElement;

  while (currentParent && !isBody(currentParent)) {
    parents.push(currentParent);
    currentParent = currentParent.parentElement;
  }

  return parents;
}

export function getDomData(originElement) {
  if (!originElement) return;
  let element = originElement;

  const stackSelector = [];

  const tempComments = {};
  let path;
  let isCommentFound = false;

  while (element.parentNode) {
    let sibCount = 0;
    let sibIndex = 0;
    let elementIndex = Number.MAX_SAFE_INTEGER;
    const childNodes = Array.from(element.parentNode.childNodes).filter(
      (node) => node.nodeType !== 3
    );
    const childNodesLength = childNodes.length;

    for (let i = 0; i < childNodesLength; i++) {
      let sib = childNodes[i];

      if (sib.nodeName === element.nodeName) {
        if (sib === element) {
          sibIndex = sibCount;
          elementIndex = i;
        }
        sibCount++;
      }

      if (i > elementIndex && !isCommentFound) {
        let nextSib = childNodes[i];

        if (isCommentWithPath(nextSib)) {
          const pathFromComment = getPathFromComment(nextSib);
          if (isClosedComment(nextSib)) {
            if (!tempComments.hasOwnProperty(pathFromComment)) {
              path = pathFromComment.trim();
              isCommentFound = true;
            } else {
              delete tempComments[pathFromComment];
            }
          } else {
            tempComments[pathFromComment] = true;
          }
        }
      }
    }

    getFullPath(stackSelector, element, sibCount, sibIndex);

    element = element.parentNode;
  }

  console.log(path);

  if (path) {
    const region = path.replace('.php', '');
    const selector = stackSelector.join(' > ');

    return [region, path, selector];
  }
}

function getFullPath(stackSelector, element, sibCount, sibIndex) {
  if (element.hasAttribute('id') && element.id !== '') {
    stackSelector.unshift(element.nodeName.toLowerCase() + '#' + element.id);
  } else if (sibCount > 1) {
    stackSelector.unshift(
      element.nodeName.toLowerCase() + ':nth-child(' + sibIndex + ')'
    );
  } else {
    stackSelector.unshift(element.nodeName.toLowerCase());
  }
}

function isComment(node) {
  if (!node) return;
  return node.nodeType === 8;
}

function isOpenedComment(comment) {
  if (!comment) return;
  return comment.nodeValue.trim().startsWith('path:');
}

function isClosedComment(comment) {
  if (!comment) return;
  return comment.nodeValue.trim().startsWith('/path:');
}

function isCommentWithPath(node) {
  if (!node) return;
  return isComment(node) && (isOpenedComment(node) || isClosedComment(node));
}

// function getNextSiblings(elem, filter) {
//   const sibs = [];
//   while ((elem = elem.nextSibling)) {
//     if (elem.nodeType === 3) continue; // text node
//     if (!filter || filter(elem)) sibs.push(elem);
//   }
//   return sibs;
// }

function getPathFromComment(comment) {
  return comment.nodeValue.trim().split(':')[1];
}
