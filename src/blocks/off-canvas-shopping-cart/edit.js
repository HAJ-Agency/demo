import { useBlockProps, InspectorControls } from '@wordpress/block-editor'
import { PanelBody, SelectControl, RangeControl } from '@wordpress/components'
import { useSelect } from '@wordpress/data'
import ServerSideRender from '@wordpress/server-side-render'

import './editor.scss'
const edit = (props) => {
    const { attributes, setAttributes, clientId } = props
    const { blockId, mainClassName, numberOfProducts, postsPerViewDesktop, postsPerViewMobile } = attributes
    React.useEffect(() => {
        if (!blockId) {
            setAttributes({
                blockId: clientId
            })
        }
        if (!mainClassName) {
            setAttributes({
                mainClassName: `tiburon-add-to-cart swiper block-editor-block-list__block wp-block ${blockProps.className} `
            })
        }
    }, [])
    const blockProps = useBlockProps()

    return (
        <>
            <svg class="shopping-cart" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M4 9H20L19.165 18.181C19.1198 18.6779 18.8906 19.14 18.5222 19.4766C18.1538 19.8131 17.673 19.9998 17.174 20H6.826C6.32704 19.9998 5.84617 19.8131 5.4778 19.4766C5.10942 19.14 4.88016 18.6779 4.835 18.181L4 9Z"
                    stroke="#303030"
                    stroke-linejoin="round"
                />
                <path
                    d="M8 11V8C8 6.93913 8.42143 5.92172 9.17157 5.17157C9.92172 4.42143 10.9391 4 12 4C13.0609 4 14.0783 4.42143 14.8284 5.17157C15.5786 5.92172 16 6.93913 16 8V11"
                    stroke="#303030"
                    stroke-linecap="round"
                />
            </svg>
        </>
    )
}

export default edit
