export function getLast(iterable) {
  return iterable[iterable.length - 1];
}

export function uniqueByKey(array, key) {
  const result = [];
  const map = new Map();

  for (const item of array) {
    if (!map.has(item[key])) {
      map.set(item[key], true);
      result.push({
        ...item
      });
    }
  }

  return result;
}
