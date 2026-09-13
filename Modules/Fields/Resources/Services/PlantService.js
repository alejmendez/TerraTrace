import axios from 'axios';

const list = async (params, signal) => {
  const response = await axios.get(route('plants.index'), {
    params: { collection: 1, ...params },
    signal,
  });

  return response.data;
};

const del = async (id) => {
  await axios.delete(route('plants.destroy', { id }));
  return true;
};

const createNote = async (data) => {
  await axios.post(route('plants.notes.store'), data);
};

export default {
  list,
  del,
  createNote,
};
