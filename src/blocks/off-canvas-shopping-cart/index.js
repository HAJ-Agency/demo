import { __ } from '@wordpress/i18n'
import { registerBlockType } from '@wordpress/blocks'
import './style.scss'
import edit from './edit.js'
/**
 * Internal dependencies
 */
import metadata from './block.json'

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType(metadata.name, {
    title: __('Off-Canvas Varukorg', 'akka'),
    // ToDo: Change the description
    description: __('Visar en off-canvas varukorg för WooCommerce', 'akka'),
    category: 'tiburon',
    icon: {
        src: (
            <svg
                width="20"
                height="20"
                viewBox="0 0 32 32"
                fill="none"
                class="wc-block-mini-cart__icon"
                aria-hidden="true"
                focusable="false"
                xmlns="http://www.w3.org/2000/svg"
            >
                <circle cx="12.6667" cy="24.6667" r="2" fill="currentColor"></circle>
                <circle cx="23.3333" cy="24.6667" r="2" fill="currentColor"></circle>
                <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M9.28491 10.0356C9.47481 9.80216 9.75971 9.66667 10.0606 9.66667H25.3333C25.6232 9.66667 25.8989 9.79247 26.0888 10.0115C26.2787 10.2305 26.3643 10.5211 26.3233 10.8081L24.99 20.1414C24.9196 20.6341 24.4977 21 24 21H12C11.5261 21 11.1173 20.6674 11.0209 20.2034L9.08153 10.8701C9.02031 10.5755 9.09501 10.269 9.28491 10.0356ZM11.2898 11.6667L12.8136 19H23.1327L24.1803 11.6667H11.2898Z"
                    fill="currentColor"
                ></path>
                <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M5.66669 6.66667C5.66669 6.11438 6.1144 5.66667 6.66669 5.66667H9.33335C9.81664 5.66667 10.2308 6.01229 10.3172 6.48778L11.0445 10.4878C11.1433 11.0312 10.7829 11.5517 10.2395 11.6505C9.69614 11.7493 9.17555 11.3889 9.07676 10.8456L8.49878 7.66667H6.66669C6.1144 7.66667 5.66669 7.21895 5.66669 6.66667Z"
                    fill="currentColor"
                ></path>
            </svg>
        )
    },
    attributes: metadata.attributes,
    edit,
    /**
     * In case of use of InnerBlocks return <InnerBlocks.Content /> instead of null
     */
    save({ attributes }) {
        return null
    }
})
