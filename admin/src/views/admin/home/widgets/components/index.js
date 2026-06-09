import {markRaw} from 'vue';
const resultComps = {}
let files = import.meta.glob('./*.vue', { eager: true });

Object.keys(files).forEach(fileName => {
  let comp = files[fileName]
  resultComps[fileName.replace(/^\.\/(.*)\.\w+$/, '$1')] = comp.default
})
export default markRaw(resultComps)
