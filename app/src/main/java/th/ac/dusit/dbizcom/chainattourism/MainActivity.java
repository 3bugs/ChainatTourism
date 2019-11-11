package th.ac.dusit.dbizcom.chainattourism;

import android.content.Context;
import android.content.Intent;
import android.graphics.Rect;
import android.os.Bundle;
import android.support.annotation.NonNull;
import android.support.v4.widget.CircularProgressDrawable;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.util.DisplayMetrics;
import android.util.TypedValue;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import com.bumptech.glide.Glide;
import com.bumptech.glide.request.RequestOptions;
import com.glide.slider.library.Animations.DescriptionAnimation;
import com.glide.slider.library.SliderLayout;
import com.glide.slider.library.SliderTypes.BaseSliderView;
import com.glide.slider.library.SliderTypes.DefaultSliderView;
import com.glide.slider.library.Tricks.ViewPagerEx;
import com.google.gson.Gson;

import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

import retrofit2.Call;
import retrofit2.Retrofit;
import th.ac.dusit.dbizcom.chainattourism.model.Place;
import th.ac.dusit.dbizcom.chainattourism.net.ApiClient;
import th.ac.dusit.dbizcom.chainattourism.net.GetRecommendResponse;
import th.ac.dusit.dbizcom.chainattourism.net.MyRetrofitCallback;
import th.ac.dusit.dbizcom.chainattourism.net.WebServices;

import static th.ac.dusit.dbizcom.chainattourism.PlaceActivity.KEY_PLACE_TYPE;
import static th.ac.dusit.dbizcom.chainattourism.PlaceDetailsActivity.KEY_PLACE_JSON;

public class MainActivity extends BaseActivity implements
        BaseSliderView.OnSliderClickListener,
        ViewPagerEx.OnPageChangeListener, View.OnClickListener {

    private SliderLayout mSlider;
    private RecyclerView mRecommendedPlacesRecyclerView, mRecommendedTemplesRecyclerView;
    private RecyclerView mRecommendedRestaurantsRecyclerView, mRecommendedOtopRecyclerView;
    private List<Place> mRecommendedPlaceList, mRecommendedTempleList;
    private List<Place> mRecommendedRestaurantList, mRecommendedOtopList;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        setupImageSlider();
        findViewById(R.id.place_layout).setOnClickListener(this);
        findViewById(R.id.temple_layout).setOnClickListener(this);
        findViewById(R.id.restaurant_layout).setOnClickListener(this);
        findViewById(R.id.otop_layout).setOnClickListener(this);

        doGetRecommend();
    }

    private void setupImageSlider() {
        mSlider = findViewById(R.id.slider);

        ArrayList<String> listUrl = new ArrayList<>();
        listUrl.add("http://5911011802058.msci.dusit.ac.th/chainat_tourism/images/banner01.png");
        listUrl.add("http://5911011802058.msci.dusit.ac.th/chainat_tourism/images/banner02.png");
        listUrl.add("http://5911011802058.msci.dusit.ac.th/chainat_tourism/images/banner03.png");

        RequestOptions requestOptions = new RequestOptions().fitCenter();

        //.diskCacheStrategy(DiskCacheStrategy.NONE)
        //.placeholder(R.drawable.placeholder)
        //.error(R.drawable.placeholder);

        for (int i = 0; i < listUrl.size(); i++) {
            DefaultSliderView sliderView = new DefaultSliderView(this);
            sliderView
                    .image(listUrl.get(i))
                    .setRequestOption(requestOptions)
                    //.setBackgroundColor(Color.WHITE)
                    .setProgressBarVisible(true)
                    .setOnSliderClickListener(this);

            //add your extra information
            sliderView.bundle(new Bundle());
            //sliderView.getBundle().putString("extra", listName.get(i));
            mSlider.addSlider(sliderView);
        }

        // set Slider Transition Animation
        mSlider.setPresetTransformer(SliderLayout.Transformer.Default);
        //mSlider.setPresetTransformer(SliderLayout.Transformer.Accordion);

        mSlider.setPresetIndicator(SliderLayout.PresetIndicators.Center_Bottom);
        mSlider.setCustomAnimation(new DescriptionAnimation());
        mSlider.setDuration(3000);
        mSlider.addOnPageChangeListener(this);
    }

    private void doGetRecommend() {
        //mProgressView.setVisibility(View.VISIBLE);

        Retrofit retrofit = ApiClient.getClient();
        WebServices services = retrofit.create(WebServices.class);

        Call<GetRecommendResponse> call = services.getRecommend();
        call.enqueue(new MyRetrofitCallback<>(
                MainActivity.this,
                null,
                null, //mProgressView,
                new MyRetrofitCallback.MyRetrofitCallbackListener<GetRecommendResponse>() {
                    @Override
                    public void onSuccess(GetRecommendResponse responseBody) {
                        mRecommendedPlaceList = responseBody.placeList;
                        mRecommendedTempleList = responseBody.templeList;
                        mRecommendedRestaurantList = responseBody.restaurantList;
                        mRecommendedOtopList = responseBody.otopList;

                        String msg = String.format(
                                Locale.getDefault(),
                                "Place: %d\nTemple: %d\nRestaurant: %d\nOTOP: %d",
                                mRecommendedPlaceList.size(),
                                mRecommendedTempleList.size(),
                                mRecommendedRestaurantList.size(),
                                mRecommendedOtopList.size()
                        );
                        //Utils.showLongToast(MainActivity.this, msg);

                        /*กำหนดประเภทสถานที่ลงใน Place*/
                        for (Place place : mRecommendedPlaceList) {
                            place.placeType = Place.PlaceType.TOUR;
                        }
                        for (Place place : mRecommendedTempleList) {
                            place.placeType = Place.PlaceType.TEMPLE;
                        }
                        for (Place place : mRecommendedRestaurantList) {
                            place.placeType = Place.PlaceType.RESTAURANT;
                        }
                        for (Place place : mRecommendedOtopList) {
                            place.placeType = Place.PlaceType.OTOP;
                        }

                        setupRecyclerView();
                    }

                    @Override
                    public void onError(String errorMessage) {
                    }
                }
        ));
    }

    private void setupRecyclerView() {
        doSetupRecyclerView(
                (RecyclerView) findViewById(R.id.recommended_places_recycler_view),
                mRecommendedPlaceList,
                R.layout.item_recommend
        );
        doSetupRecyclerView(
                (RecyclerView) findViewById(R.id.recommended_temples_recycler_view),
                mRecommendedTempleList,
                R.layout.item_recommend
        );
        doSetupRecyclerView(
                (RecyclerView) findViewById(R.id.recommended_restaurants_recycler_view),
                mRecommendedRestaurantList,
                R.layout.item_recommend
        );
        doSetupRecyclerView(
                (RecyclerView) findViewById(R.id.recommended_otop_recycler_view),
                mRecommendedOtopList,
                R.layout.item_recommend_otop
        );
    }

    private void doSetupRecyclerView(RecyclerView recyclerView, List<Place> placeList, int layoutResId) {
        LinearLayoutManager lm
                = new LinearLayoutManager(this, LinearLayoutManager.HORIZONTAL, false);
        RecommendAdapter adapter = new RecommendAdapter(
                this,
                placeList,
                layoutResId
        );
        recyclerView.setLayoutManager(lm);
        recyclerView.addItemDecoration(new SpacingDecoration(this));
        recyclerView.setAdapter(adapter);
    }

    @Override
    protected void onStop() {
        mSlider.stopAutoCycle();
        super.onStop();
    }

    @Override
    public void onClick(View view) {
        Place.PlaceType placeType = null;

        switch (view.getId()) {
            case R.id.place_layout:
                placeType = Place.PlaceType.TOUR;
                break;
            case R.id.temple_layout:
                placeType = Place.PlaceType.TEMPLE;
                break;
            case R.id.restaurant_layout:
                placeType = Place.PlaceType.RESTAURANT;
                break;
            case R.id.otop_layout:
                placeType = Place.PlaceType.OTOP;
                break;
        }

        Intent intent = new Intent(MainActivity.this, PlaceActivity.class);
        intent.putExtra(KEY_PLACE_TYPE, placeType);
        startActivity(intent);
    }

    @Override
    public void onSliderClick(BaseSliderView slider) {

    }

    @Override
    public void onPageScrolled(int position, float positionOffset, int positionOffsetPixels) {

    }

    @Override
    public void onPageSelected(int position) {

    }

    @Override
    public void onPageScrollStateChanged(int state) {

    }

    private static class RecommendAdapter extends RecyclerView.Adapter<RecommendAdapter.ViewHolder> {

        private final Context mContext;
        private final List<Place> mPlaceList;
        private final int mLayoutResId;

        RecommendAdapter(Context context, List<Place> placeList, int layoutResId) {
            mContext = context;
            mPlaceList = placeList;
            mLayoutResId = layoutResId;
        }

        @NonNull
        @Override
        public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
            View view = LayoutInflater.from(parent.getContext()).inflate(
                    mLayoutResId, parent, false
            );
            return new ViewHolder(view);
        }

        @Override
        public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
            final Place place = mPlaceList.get(position);
            holder.mPlace = place;

            CircularProgressDrawable circularProgressDrawable = new CircularProgressDrawable(mContext);
            circularProgressDrawable.setStrokeWidth(5f);
            circularProgressDrawable.setCenterRadius(30f);
            circularProgressDrawable.start();

            Glide.with(mContext)
                    .load(ApiClient.IMAGE_BASE_URL.concat(place.listImage))
                    .placeholder(circularProgressDrawable)
                    .into(holder.mImageView);

            holder.mTextView.setText(place.name);
        }

        @Override
        public int getItemCount() {
            return mPlaceList.size();
        }

        class ViewHolder extends RecyclerView.ViewHolder {

            private final View mRootView;
            private final ImageView mImageView;
            private final TextView mTextView;

            private Place mPlace;

            ViewHolder(View itemView) {
                super(itemView);

                mRootView = itemView;
                mImageView = itemView.findViewById(R.id.image_view);
                mTextView = itemView.findViewById(R.id.text_view);

                mRootView.setOnClickListener(new View.OnClickListener() {
                    @Override
                    public void onClick(View view) {
                        Intent intent = new Intent(mContext, PlaceDetailsActivity.class);
                        intent.putExtra(KEY_PLACE_JSON, new Gson().toJson(mPlace));
                        mContext.startActivity(intent);
                    }
                });
            }
        }
    }

    public class SpacingDecoration extends RecyclerView.ItemDecoration {

        private final static int MARGIN_LEFT_IN_DP = 0;
        private final static int MARGIN_RIGHT_IN_DP = 16;
        private final int mMarginLeft, mMarginRight;

        SpacingDecoration(@NonNull Context context) {
            DisplayMetrics metrics = context.getResources().getDisplayMetrics();
            mMarginLeft = (int) TypedValue.applyDimension(
                    TypedValue.COMPLEX_UNIT_DIP,
                    MARGIN_LEFT_IN_DP,
                    metrics
            );
            mMarginRight = (int) TypedValue.applyDimension(
                    TypedValue.COMPLEX_UNIT_DIP,
                    MARGIN_RIGHT_IN_DP,
                    metrics
            );
        }

        @Override
        public void getItemOffsets(@NonNull Rect outRect, @NonNull View view,
                                   @NonNull RecyclerView parent,
                                   @NonNull RecyclerView.State state) {
            final int itemPosition = parent.getChildAdapterPosition(view);
            if (itemPosition == RecyclerView.NO_POSITION) {
                return;
            }
            if (itemPosition == 0) {
                outRect.left = mMarginLeft;
            }
            final RecyclerView.Adapter adapter = parent.getAdapter();
            if ((adapter != null) && (itemPosition == adapter.getItemCount() - 1)) {
                outRect.right = mMarginRight;
            }
        }
    }
}
