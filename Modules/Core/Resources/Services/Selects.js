import axios from 'axios';

export const getDataSelect = async (entity, filter) => {
  const response = await axios.get(route('selects.index', { entity }), {
    params: {
      filter,
    },
  });
  return response.data;
};

export const getDataSelects = async (entities) => {
  const response = await axios.get(route('selects.multiple'), {
    params: {
      entities: JSON.stringify(entities),
    },
  });
  return response.data;
};
