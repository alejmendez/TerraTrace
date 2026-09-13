import axios from 'axios';

const list = async (params, signal) => {
  const response = await axios.get(route('fields.index'), {
    params: { collection: 1, ...params },
    signal,
  });

  return response.data;
};

const del = async (id) => {
  await axios.delete(route('fields.destroy', { id }));
  return true;
};

export default {
  list,
  del,
};
