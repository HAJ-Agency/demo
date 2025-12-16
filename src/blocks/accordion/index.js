/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import classNames from "classnames";
import { __ } from "@wordpress/i18n";
import { registerBlockType } from "@wordpress/blocks";
import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
import { PanelBody, PanelRow, SelectControl, RadioControl, CheckboxControl, ToggleControl } from "@wordpress/components";
import { Fragment } from "@wordpress/element";

import "./style.scss";
import "./editor.scss";

/**
 * Internal dependencies
 */
import metadata from "./block.json";

const mainClassNameGenerator = ({ attributes, blockProps }) => {
   const { isTwoColumns } = attributes;

   let classes = `wp-block-accordion block-editor-block-list__block wp-block ${blockProps.className}`;
   if (isTwoColumns) {
      classes = classNames(classes, `is-two-columns`);
   }
   return classes;
};

const blockAttributes = {
   blockId: {
      type: "string",
   },
   mainClassName: {
      type: "string",
   },
   sourceType: {
      type: "string",
   },
   items: {
      type: "array",
      default: [],
   },
   postType: {
      type: "string",
   },
   postTypeLimit: {
      type: "number",
   },
   availablePostTypes: {
      type: "array",
   },
   postTypeTaxonomies: {
      type: "object",
      default: {},
   },
   useTaxonomy: {
      type: "boolean",
      default: false,
   },
   taxonomy: {
      type: "string",
      default: "category",
   },
   isFilter: {
      type: "boolean",
      default: false,
   },
   taxonomyToFilter: {
      type: "string",
      default: "",
   },
   isTwoColumns: {
      type: "boolean",
      default: false,
   },
   taxonomyFilterMethod: {
      type: "string",
      default: "exclude",
   },
   selectedTaxonomyItems: {
      type: "array",
      default: [],
   },
   selectedTaxonomy: {
      type: "string",
      default: "",
   },
};

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType(metadata.name, {
   title: __("Accordion", "demo"),
   description: __("Ett block som visar en lista med poster i en accordion.", "demo"),
   category: "design",
   icon: {
      src: (
         <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="40" height="40" fill="#174845" />
            <path
               d="M33.3796 23.3303C32.6901 22.7883 31.832 22.1139 30.2112 22.1139C29.5312 22.1139 28.9857 22.2328 28.5303 22.4107C28.0786 18.7511 26.4201 15.3702 23.7622 12.7532C20.6945 9.73262 16.6385 8.06912 12.3417 8.06912H11.3428V22.3633C10.9137 22.212 10.4069 22.1139 9.78912 22.1139C8.16829 22.1139 7.31016 22.7883 6.62064 23.3302C6.03708 23.7889 5.6154 24.1203 4.68335 24.1203V26.1266C6.30418 26.1266 7.16232 25.4522 7.85183 24.9103C8.4354 24.4516 8.85707 24.1203 9.78912 24.1203C10.7212 24.1203 11.1428 24.4516 11.7264 24.9103C12.4159 25.4522 13.274 26.1266 14.8949 26.1266C16.5157 26.1266 17.3738 25.4522 18.0633 24.9102C18.6468 24.4516 19.0684 24.1203 20.0004 24.1203C20.9323 24.1203 21.3539 24.4516 21.9374 24.9103C22.6269 25.4522 23.4851 26.1266 25.1058 26.1266C26.7265 26.1266 27.5847 25.4522 28.2741 24.9102C28.8577 24.4516 29.2793 24.1203 30.2112 24.1203C31.1432 24.1203 31.5647 24.4516 32.1483 24.9103C32.8378 25.4522 33.6959 26.1266 35.3167 26.1266V24.1203C34.3847 24.1203 33.9631 23.7889 33.3796 23.3303ZM23.1687 23.3303C22.4792 22.7883 21.6212 22.1139 20.0004 22.1139C18.3796 22.1139 17.5215 22.7883 16.832 23.3303C16.2485 23.7889 15.8268 24.1203 14.8949 24.1203C14.176 24.1203 13.7607 23.9226 13.3406 23.622V10.1108C20.4514 10.6144 26.2297 16.449 26.6349 23.6401C26.2232 23.9307 25.8103 24.1203 25.1058 24.1203C24.1739 24.1203 23.7523 23.7889 23.1687 23.3303Z"
               fill="#E3FB53"
            />
            <path
               d="M30.2112 27.9002C28.5904 27.9002 27.7323 28.5776 27.0429 29.122C26.4594 29.5827 26.0378 29.9155 25.1058 29.9155C24.1739 29.9155 23.7522 29.5827 23.1687 29.1219C22.4792 28.5776 21.6212 27.9002 20.0004 27.9002C18.3796 27.9002 17.5215 28.5776 16.832 29.122C16.2485 29.5827 15.8268 29.9155 14.8949 29.9155C13.9628 29.9155 13.5412 29.5827 12.9577 29.1219C12.2681 28.5776 11.41 27.9002 9.78912 27.9002C8.16829 27.9002 7.31016 28.5776 6.62064 29.1219C6.03708 29.5827 5.6154 29.9155 4.68335 29.9155V31.9309C6.30418 31.9309 7.16232 31.2534 7.85183 30.7091C8.4354 30.2484 8.85707 29.9155 9.78912 29.9155C10.7212 29.9155 11.1428 30.2484 11.7264 30.7091C12.4159 31.2534 13.274 31.9309 14.8949 31.9309C16.5157 31.9309 17.3738 31.2534 18.0633 30.709C18.6468 30.2484 19.0684 29.9155 20.0004 29.9155C20.9323 29.9155 21.3539 30.2484 21.9374 30.7091C22.6269 31.2534 23.4851 31.9309 25.1058 31.9309C26.7265 31.9309 27.5847 31.2534 28.2741 30.709C28.8577 30.2484 29.2793 29.9155 30.2112 29.9155C31.1432 29.9155 31.5647 30.2484 32.1483 30.7091C32.8378 31.2534 33.6959 31.9309 35.3167 31.9309V29.9155C34.3847 29.9155 33.9631 29.5827 33.3796 29.1219C32.6901 28.5776 31.832 27.9002 30.2112 27.9002Z"
               fill="#E3FB53"
            />
         </svg>
      ),
   },
   attributes: blockAttributes,
   edit({ attributes, setAttributes, clientId }) {
      const {
         blockId,
         mainClassName,
         sourceType,
         postType,
         postTypeLimit,
         postTypeTaxonomies,
         isFilter,
         taxonomyToFilter,
         isTwoColumns,
         taxonomyFilterMethod,
         selectedTaxonomyItems,
         selectedTaxonomy,
      } = attributes;
      const taxonomyFilterOptions = [
         {
            value: "exclude",
            label: "Exclude",
         },
         {
            value: "include",
            label: "Include",
         },
         {
            value: "all",
            label: "All",
         },
      ];
      // ToDo: Implement to prevent thinking changes has been made when visiting editor
      const [postTypeItems, setPostTypeItemsData] = React.useState([]);
      const [availablePostTypes, setAvailablePostTypes] = React.useState([]);

      React.useEffect(() => {
         setMainClassNames();
      }, [attributes]);
      React.useEffect(() => {
         if (!blockId) {
            setAttributes({
               blockId: clientId,
            });
         }
         if (!postTypeLimit) {
            setAttributes({
               postTypeLimit: -1,
            });
         }
         if (!postType) {
            setAttributes({
               postType: "post",
            });
            setPostTypeTaxonomies("post");
         }
         // Fetch taxonomies only if they are missing (ensures they persist on reload)
         if (!postTypeTaxonomies || Object.keys(postTypeTaxonomies).length === 0) {
            setPostTypeTaxonomies(postType);
         }
         if (availablePostTypes.length === 0) {
            let path = `/hapi/v1/post-types/`;
            new wp.apiRequest({
               path: path,
            }).then((data) => {
               setAvailablePostTypes(data);
            });
         }
         if (postTypeItems.length === 0) {
            setPostTypeItems(postType, postTypeLimit, postTypeTaxonomies);
         }
      }, []);

      React.useEffect(() => {
         setPostTypeItems(postType, postTypeLimit, postTypeTaxonomies);
      }, [postType, sourceType, postTypeTaxonomies, postTypeLimit, taxonomyFilterMethod, selectedTaxonomyItems]);

      const blockProps = useBlockProps();

      function setMainClassNames() {
         const newMainClassName = mainClassNameGenerator({
            attributes,
            blockProps,
         });
         setAttributes({
            mainClassName: newMainClassName,
         });
      }

      function setPostTypeItems(postType, postTypeLimit, postTypeTaxonomies) {
         let postData = {
            limit: postTypeLimit,
         };
         let postTypePath = `/hapi/v1/${postType}`;
         if (selectedTaxonomyItems && selectedTaxonomyItems.length > 0) {
            if (taxonomyFilterMethod === "exclude") {
               postTypeTaxonomies = Object.fromEntries(
                  Object.entries(postTypeTaxonomies).map(([taxonomy, items]) => [
                     taxonomy,
                     items.map((item) => ({
                        ...item,
                        selected: !selectedTaxonomyItems.includes(item.value),
                     })),
                  ])
               );
            } else if (taxonomyFilterMethod === "include") {
               postTypeTaxonomies = Object.fromEntries(
                  Object.entries(postTypeTaxonomies).map(([taxonomy, items]) => [
                     taxonomy,
                     items.map((item) => ({
                        ...item,
                        selected: selectedTaxonomyItems.includes(item.value),
                     })),
                  ])
               );
            } else {
               postTypeTaxonomies = Object.fromEntries(
                  Object.entries(postTypeTaxonomies).map(([taxonomy, items]) => [
                     taxonomy,
                     items.map((item) => ({
                        ...item,
                        selected: true,
                     })),
                  ])
               );
            }
         }
         if (postTypeTaxonomies) {
            postData.taxData = postTypeTaxonomies;
         }
         new wp.apiRequest({
            path: postTypePath,
            method: "POST",
            data: postData,
         }).then((data) => {
            setPostTypeItemsData(data);
         });
      }

      function setPostTypeTaxonomies(postType) {
         let taxonomyPath = `/hapi/v1/tax/${postType}`;
         new wp.apiRequest({
            path: taxonomyPath,
         }).then((data) => {
            setAttributes({
               postTypeTaxonomies: data,
               selectedTaxonomy: Object.keys(data)[0],
            });
         });
      }

      return (
         <Fragment>
            <InspectorControls>
               <PanelBody title={__("Source", "demo")}>
                  <PanelRow className={classNames(["block-settings"])}>
                     <div className={classNames([""])}>
                        <SelectControl
                           __nextHasNoMarginBottom
                           label={__("Post Type", "demo")}
                           value={postType}
                           options={availablePostTypes}
                           onChange={(value) => {
                              setAttributes({ postType: value });
                              setPostTypeTaxonomies(value);
                           }}
                        />
                     </div>
                  </PanelRow>
                  {postTypeTaxonomies && (
                     <>
                        <label className={classNames(["custom-label"])}>{__("Exclude or include from category", "demo")}</label>
                        <PanelRow>
                           <RadioControl
                              selected={taxonomyFilterMethod}
                              options={taxonomyFilterOptions.map((item) => ({
                                 label: item.label,
                                 value: item.value,
                              }))}
                              onChange={(value) => setAttributes({ taxonomyFilterMethod: value })}
                           />
                        </PanelRow>
                        {taxonomyFilterMethod !== "all" && (
                           <>
                              <PanelRow>
                                 <SelectControl
                                    label={__("Taxonomy", "demo")}
                                    value={selectedTaxonomy}
                                    options={Object.entries(postTypeTaxonomies).map(([taxonomy]) => {
                                       return {
                                          label: taxonomy,
                                          value: taxonomy,
                                       };
                                    })}
                                    onChange={(value) => {
                                       setAttributes({
                                          selectedTaxonomy: value,
                                       });
                                    }}
                                 />
                              </PanelRow>

                              {postTypeTaxonomies && postTypeTaxonomies[selectedTaxonomy] && (
                                 <PanelRow>
                                    <SelectControl
                                       multiple
                                       value={selectedTaxonomyItems}
                                       options={postTypeTaxonomies[selectedTaxonomy].map((item) => ({
                                          label: item.label,
                                          value: item.value,
                                       }))}
                                       onChange={(values) => {
                                          setAttributes({
                                             selectedTaxonomyItems: values,
                                          });
                                       }}
                                    />
                                 </PanelRow>
                              )}
                           </>
                        )}
                     </>
                  )}
               </PanelBody>
               {/* <PanelBody title={__("Filter", "demo")}>
                  <ToggleControl
                     label={__("Use Filtering", "demo")}
                     checked={isFilter}
                     onChange={() => {
                        setAttributes({ isFilter: !isFilter });
                     }}
                  />
                  {isFilter && (
                     <PanelRow className={classNames(["block-settings flex-settings flex-wrap-full"])}>
                        <label className={classNames(["custom-label"])}>{__("Select Category", "demo")}</label>
                        {postTypeTaxonomies &&
                           Object.entries(postTypeTaxonomies).map(([taxonomy, taxonomyData], index) => {
                              return (
                                 <>
                                    <CheckboxControl
                                       __nextHasNoMarginBottom
                                       key={index}
                                       label={taxonomy}
                                       checked={taxonomyToFilter === taxonomy}
                                       onChange={(isChecked) => {
                                          if (isChecked) {
                                             setAttributes({
                                                taxonomyToFilter: taxonomy,
                                             });
                                          } else {
                                             setAttributes({
                                                taxonomyToFilter: "",
                                             });
                                          }
                                       }}
                                    />
                                 </>
                              );
                           })}
                     </PanelRow>
                  )}
               </PanelBody> */}
               {/* <PanelBody title={__('Appearance', 'demo')}>
                  <PanelRow>
                     <ToggleControl
                        label={__('2 columns', 'demo')}
                        checked={isTwoColumns}
                        onChange={() => {
                           setAttributes({ isTwoColumns: !isTwoColumns });
                        }}
                     />
                  </PanelRow>
               </PanelBody> */}
            </InspectorControls>
            <div {...blockProps} dataBlockId={blockId} className={classNames([mainClassName])}>
               {isFilter && (
                  <div class="accordion-filter-buttons">
                     {postTypeTaxonomies[selectedTaxonomy] && postTypeTaxonomies[selectedTaxonomy].map((item, index) => <button class={index === 0 ? "is-active" : ""}>{item.label}</button>)}
                  </div>
               )}
               <div className={classNames(["wp-block-accordion block-editor-block-list__layout"])}>
                  {postTypeItems.map((item, index) => {
                     return (
                        <div key={index} className={classNames(["wp-block-accordion__item post-type-item", `post-type-item-${index}`])}>
                           <div className="wp-block-accordion__item-title">
                              <h3 className="">{item.title}</h3>
                              <div className="wp-block-accordion-icon">
                                 <svg id="b" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="20px" height="20px" fill="none">
                                    <g id="c">
                                       <g id="d">
                                          <path
                                             class="e"
                                             d="m15,2c7.17,0,13,5.83,13,13s-5.83,13-13,13S2,22.17,2,15,7.83,2,15,2m0-2C6.72,0,0,6.72,0,15s6.72,15,15,15,15-6.72,15-15S23.28,0,15,0h0Z"
                                             stroke-width="0px"
                                          />
                                          <line class="f" x1="10" y1="0" x2="10" y2="20" fill="none" stroke="#000" stroke-linecap="round" stroke-width="2px" />
                                          <line class="g" x1="20" y1="10" x2="0" y2="10" fill="none" stroke="#000" stroke-linecap="round" stroke-width="2px" />
                                       </g>
                                    </g>
                                 </svg>
                              </div>
                           </div>
                           <div className="wp-block-accordion__item-content has-1-125-font-size">{item.excerpt}</div>
                        </div>
                     );
                  })}
               </div>
            </div>
         </Fragment>
      );
   },
   save({ attributes }) {
      return null;
   },
});
