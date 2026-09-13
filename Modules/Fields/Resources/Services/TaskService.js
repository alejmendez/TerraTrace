import axios from 'axios';

const list = async (lazyParams) => {
  const response = await axios.get(route('tasks.index'), {
    params: {
      dt_params: JSON.stringify(lazyParams),
    },
  });

  return response.data;
};

const del = async (id) => {
  await axios.delete(route('tasks.destroy', { id }));
  return true;
};

export default {
  list,
  del,
};
