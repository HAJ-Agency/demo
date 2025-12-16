import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, RangeControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';

import './editor.scss';
const edit = (props) => {
   const { attributes, setAttributes, clientId } = props;
   const {
      blockId,
      mainClassName,
      postType,
      numberOfPosts,
      postsPerViewDesktop,
      postsPerViewMobile,
   } = attributes;
   React.useEffect(() => {
      if (!blockId) {
         setAttributes({
            blockId: clientId,
         });
      }
      if (!mainClassName) {
         setAttributes({
            mainClassName: `scrollbar-slider swiper block-editor-block-list__block wp-block ${blockProps.className} `,
         });
      }
   }, []);
   const postTypes = useSelect((select) => {
      const types = select('core').getPostTypes({ per_page: -1 });
      return types
         ? types
              .filter((type) =>
                 !type.viewable || type.slug === 'attachment' ? false : true,
              )
              .map((type) => ({ label: type.name, value: type.slug }))
         : [];
   }, []);
   const blockProps = useBlockProps();

   return (
      <>
         <InspectorControls>
            <PanelBody title="Slider Inställningar">
               <SelectControl
                  label="Post Typ"
                  value={postType}
                  options={postTypes}
                  onChange={(val) => setAttributes({ postType: val })}
               />
               <RangeControl
                  label="Antal inlägg"
                  value={numberOfPosts}
                  onChange={(val) => setAttributes({ numberOfPosts: val })}
                  min={1}
                  max={20}
               />
               <RangeControl
                  label="Synliga Inlägg Dator"
                  value={postsPerViewDesktop}
                  onChange={(val) =>
                     setAttributes({ postsPerViewDesktop: val })
                  }
                  min={1}
                  max={5}
               />
               <RangeControl
                  label="Synliga Inlägg Mobil"
                  value={postsPerViewMobile}
                  onChange={(val) => setAttributes({ postsPerViewMobile: val })}
                  min={1}
                  max={5}
               />
            </PanelBody>
         </InspectorControls>

         <ServerSideRender
            block="tiburon/scrollbar-slider"
            attributes={attributes}
         />
      </>
   );
};

export default edit;
