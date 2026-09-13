import axios from 'axios';

function transformFilters(obj) {
  let newObj = JSON.parse(JSON.stringify(obj));

  for (let key in newObj.filters) {
    let filter = newObj.filters[key];

    if (filter.value && typeof filter.value === 'object' && filter.value !== null) {
      filter.value = filter.value.value;
    }
  }

  return newObj;
}

const list = async (url, params) => {
  return await axios.get(url, {
    params: {
      dt_params: JSON.stringify(transformFilters(params)),
    },
  });
};

export default {
  list,
};
