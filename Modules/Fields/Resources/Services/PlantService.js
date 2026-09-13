import axios from 'axios';
import datatable from '@Core/Services/Datatable';

const list = async (lazyParams) => {
  const response = await datatable.list(route('plants.index'), lazyParams);

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
