import axios from 'axios';

export const findByCode = async (code) => {
  const response = await axios.get(route('harvests_details.find_by_code', { code }));
  return response.data;
};
